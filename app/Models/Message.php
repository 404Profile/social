<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    const MESSAGE = 0;
    const IMAGE_MESSAGE = 1;
    const DOCUMENT_MESSAGE = 2;
    const AUDIO_MESSAGE = 3;
    const VIDEO_MESSAGE = 4;
    const THREAD_CREATED = 101;
    const THREAD_DELETED = 102;
    const THREAD_RENAMED = 103;
    const THREAD_AVATAR_CHANGED = 104;
    const PARTICIPANT_LEFT_GROUP = 200;
    const PARTICIPANTS_ADDED = 201;
    const PARTICIPANT_REMOVED = 202;
    const DEMOTED_ADMIN = 301;
    const PROMOTED_ADMIN = 302;

    const NonSystemTypes = [
        self::MESSAGE,
        self::IMAGE_MESSAGE,
        self::DOCUMENT_MESSAGE,
        self::AUDIO_MESSAGE,
        self::VIDEO_MESSAGE,
    ];

    const TYPE = [
        0 => 'MESSAGE',
        1 => 'IMAGE_MESSAGE',
        2 => 'DOCUMENT_MESSAGE',
        3 => 'AUDIO_MESSAGE',
        4 => 'VIDEO_MESSAGE',

        101 => 'THREAD_CREATED',
        102 => 'THREAD_DELETED',
        103 => 'THREAD_RENAMED',
        104 => 'THREAD_AVATAR_CHANGED',

        201 => 'PARTICIPANTS_ADDED',
        202 => 'PARTICIPANT_REMOVED',

        301 => 'DEMOTED_ADMIN',
        302 => 'PROMOTED_ADMIN',
    ];

    protected $fillable = [
        'thread_id',
        'user_id',
        'type',
        'body',
        'reply_to_id',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function isImage(): bool
    {
        return $this->type === self::IMAGE_MESSAGE;
    }

    public function isDocument(): bool
    {
        return $this->type === self::DOCUMENT_MESSAGE;
    }

    public function isAudio(): bool
    {
        return $this->type === self::AUDIO_MESSAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::VIDEO_MESSAGE;
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function recipients(): BelongsTo
    {
        return $this->participants()->where('user_id', '!=', $this->user_id);
    }

    public function participants(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function notSystemMessage(): bool
    {
        return !$this->isSystemMessage();
    }

    public function isSystemMessage(): bool
    {
        return !in_array($this->type, self::NonSystemTypes);
    }

    public function getSentAtAttribute()
    {
        if (Carbon::create($this->created_at)->addDays(1)->format('Y-m-d H:i:s') > Carbon::now()) {
            return $this->created_at->format('H:i:s');
        } else {
            return $this->created_at->format('Y-m-d');
        }
    }

    public function scopeImage(Builder $query): Builder
    {
        return $query->where('type', '=', self::IMAGE_MESSAGE);
    }

    public function scopeDocument(Builder $query): Builder
    {
        return $query->where('type', '=', self::DOCUMENT_MESSAGE);
    }

    public function scopeAudio(Builder $query): Builder
    {
        return $query->where('type', '=', self::AUDIO_MESSAGE);
    }

    public function scopeVideo(Builder $query): Builder
    {
        return $query->where('type', '=', self::VIDEO_MESSAGE);
    }

}
