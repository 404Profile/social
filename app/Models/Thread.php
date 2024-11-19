<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class Thread extends Model
{
    use HasFactory;

    const PRIVATE = 1;
    const GROUP = 2;
    const PINNED_PRIVATE = 3;
    const TYPE = [
        1 => 'PRIVATE',
        2 => 'GROUP',
        3 => 'PINNED_PRIVATE',
    ];

    protected $fillable = [
        'title',
        'type',
        'image',
    ];

    protected $appends = [
        'userUnreadMessagesCount', 'latestMessage', 'lastMessageTimeAgo', 'imageThread', 'titleThread',
        'userPrivateThread', 'participantsCount', 'authUserAdminType',
    ];

    public static function getAllLatest()
    {
        return static::latest('updated_at');
    }

    public static function getBySubject($subject)
    {
        return static::where('subject', 'like', $subject)->get();
    }

    public function getAuthUserAdminTypeAttribute()
    {
        return $this->getParticipantFromUser(Auth::id())->admin;
    }

    public function getParticipantFromUser($userId)
    {
        return $this->participants()->where('user_id', $userId)->firstOrFail();
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'thread_id', 'id');
    }

    public function scopeForUser(Builder $query, $userId)
    {
        $participantsTable = 'participants';
        $threadsTable = 'threads';

        return $query->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable.'.thread_id')
            ->where($participantsTable.'.user_id', $userId)
            ->whereNull($participantsTable.'.deleted_at')
            ->select($threadsTable.'.*');
    }

    public function getImageThreadAttribute()
    {
        if ($this->isGroup()) {
            return $this->imageGroupThread();
        } elseif ($this->isPrivate()) {
            return $this->imagePrivateThread();
        } elseif ($this->isPinnedPrivate()) {
            return Auth::user()->profile_photo_url;
        }
    }

    public function isGroup(): bool
    {
        return $this->type == self::GROUP;
    }

    public function imageGroupThread()
    {
        if ($this->isGroup()) {
            if ($this->image == null) {
                return asset('assets/threadGroup.jpg');
            } else {
                return url('storage/threads/'.$this->image);
            }
        }
    }

    public function isPrivate(): bool
    {
        return $this->type == self::PRIVATE;
    }

    public function imagePrivateThread()
    {
        if ($this->isPrivate()) {
            $image = '';

            foreach ($this->recipient() as $recipient):
                if ($recipient->user['id'] !== Auth::id()) {
                    $image = $recipient->user['profile_photo_url'];
                }
            endforeach;

            return $image;
        }
    }

    public function recipient(): array|Participant
    {
        $recipients = array();
        $this->load('participants.user');
        foreach ($this->participants as $participant):
            array_push($recipients, $participant);
        endforeach;
        return $recipients;
    }

    public function isPinnedPrivate(): bool
    {
        return $this->type == self::PINNED_PRIVATE;
    }

    public function getTitleThreadAttribute()
    {
        $title = '';
        if ($this->isGroup()) {
            $title = $this->title;
        } elseif ($this->isPrivate()) {
            foreach ($this->recipient() as $recipient):
                if ($recipient->user['id'] !== Auth::id()) {
                    $title = $recipient->user['fullName'];
                }
            endforeach;
        } elseif ($this->isPinnedPrivate()) {
            if (strlen(Auth::user()->fullName) > 40) {
                return substr(Auth::user()->fullName, 0, 40).'...';
            }

            return Auth::user()->fullName;
        }

        if (strlen($title) > 40) {
            return substr($title, 0, 40).'...';
        }

        return $title;
    }

    public function getUserPrivateThreadAttribute()
    {
        if ($this->isPrivate()) {
            foreach ($this->recipient() as $recipient):
                $user = $recipient->user;
            endforeach;
            return $user;
        }
    }

    public function getLastMessageTimeAgoAttribute()
    {
        if ($this->messages()->count() != null) {
            return $this->getLatestMessageAttribute()->created_at->diffForHumans();
        }
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function users()
    {
        return $this
            ->belongsToMany(User::class, 'participants', 'thread_id', 'user_id')
            ->withTimestamps()->withPivot('admin')->orderByPivot('created_at');
    }

    public function participantsUserIds($userId = null)
    {
        $users = $this->participants()->withTrashed()->select('user_id')->get()->map(function ($participant) {
            return $participant->user_id;
        });

        if ($userId !== null) {
            $users->push($userId);
        }

        return $users->toArray();
    }

    public function getParticipantsCountAttribute()
    {
        return $this->participants()->count();
    }

    public function addParticipant($userId)
    {
        $userIds = is_array($userId) ? $userId : func_get_args();

        collect($userIds)->each(function ($userId) {
            Participant::query()->firstOrCreate([
                'user_id' => $userId,
                'thread_id' => $this->id,
            ]);
        });
    }

    public function removeParticipant($userId)
    {
        $userIds = is_array($userId) ? $userId : func_get_args();

        Participant::query()->where('thread_id', $this->id)->whereIn('user_id', $userIds)->delete();
    }

    public function markAsRead($userId)
    {
        try {
            $participant = $this->getParticipantFromUser($userId);
            $participant->last_read = now()->format('Y-m-d H:i:s.u');
            $participant->save();
        } catch (ModelNotFoundException $e) { // @codeCoverageIgnore
            // do nothing
        }
    }

    public function isUnread($userId)
    {
        try {
            $participant = $this->getParticipantFromUser($userId);

            if ($participant->last_read === null || $this->updated_at->gt($participant->last_read)) {
                return true;
            }
        } catch (ModelNotFoundException $e) { // @codeCoverageIgnore
            // do nothing
        }

        return false;
    }

    public function activateAllParticipants()
    {
        $participants = $this->participants()->onlyTrashed()->get();
        foreach ($participants as $participant) {
            $participant->restore();
        }
    }

    public function hasParticipant($userId)
    {
        $participants = $this->participants()->where('user_id', '=', $userId);

        return $participants->count() > 0;
    }

    public function userUnreadMessagesCount($userId)
    {
        return $this->userUnreadMessages($userId)->count();
    }

    public function userUnreadMessages($userId)
    {
        $messages = $this->messages()->where('user_id', '!=', $userId)->get();

        try {
            $participant = $this->getParticipantFromUser($userId);
        } catch (ModelNotFoundException $e) {
            return collect();
        }

        if (!$participant->last_read) {
            return $messages;
        }

        return $messages->filter(function ($message) use ($participant) {
            return $message->updated_at->gt($participant->last_read);
        });
    }

    public function getUserUnreadMessagesCountAttribute()
    {
        return $this->userUnreadMessages(Auth::id())->count();
    }
}
