<?php

namespace App\Http\Controllers;

use App\Events\DeleteChatMessageEvent;
use App\Events\MessageUpdatedEvent;
use App\Events\NewChatMessageEvent;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ThreadController extends Controller
{
    public function index()
    {
        $threads = Thread::query()
            ->whereNot('type', 3)
            ->with('participants')
            ->whereRelation('participants', 'user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        $pinnedPrivate = Thread::query()
            ->where('type', 3)
            ->with('participants')
            ->whereRelation('participants', 'user_id', Auth::id())
            ->first();

        return Inertia::render('User/Threads/Index', [
            'threads' => $threads,
            'pinnedPrivate' => $pinnedPrivate,
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
        ])->validate();

        $thread = new Thread();
        $thread->type = 2;
        $thread->title = $request['title'];
        $thread->save();

        $participant = new Participant();
        $participant->thread_id = $thread->id;
        $participant->user_id = Auth::user()->id;
        $participant->admin = 2;
        $participant->muted = false;
        $participant->last_read = now()->format('Y-m-d H:i:s.u');
        $participant->save();

        $message = new Message();
        $message->thread_id = $thread->id;
        $message->user_id = Auth::user()->id;
        $message->type = 101;
        $message->body = Auth::user()->fullName.' создал чат.';
        $message->save();

        return Redirect::route('threads.show', $thread->id)->with('success', 'Чат успешно создан.');
    }

    public function show(Thread $thread)
    {
        $thread->load('users');

        return Inertia::render('User/Threads/Show', [
            'thread' => $thread,
        ]);
    }

    public function message(Request $request, Thread $thread)
    {
        Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:65535'],
            'reply_to_id' => ['nullable', 'numeric'],
        ])->validate();

        $thread->load('participants');

        $message = $thread->messages()->create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'type' => 0,
            'body' => $request['body'],
            'reply_to_id' => $request['reply_to_id'] ? $request['reply_to_id'] : null,
        ]);

        $thread->touch();

        $message->load('user')->load('replyTo');

        NewChatMessageEvent::dispatch($message);

        return $message->load('thread');
    }

    public function updateMessage(Request $request, Thread $thread)
    {
        $message = Message::query()->where('id', $request['message'])->firstOrFail();

        $message->update([
            'body' => $request['body'],
            'edited' => true,
        ]);

        $message->load('user')->load('replyTo');

        MessageUpdatedEvent::dispatch($message);

        return $message->load('thread');
    }

    public function update(Request $request, Thread $thread)
    {
        Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
        ])->validate();

        $thread->update([
            'title' => $request['title'],
        ]);

        $message = new Message();
        $message->thread_id = $thread->id;
        $message->user_id = Auth::user()->id;
        $message->type = 103;
        $message->body = Auth::user()->fullName.' переименовал чат на "'.$thread->title.'"';
        $message->save();

        NewChatMessageEvent::dispatch($message);

        return Redirect::back()->with('success', 'Название беседы успешно изменено.');
    }

    public function deleteMessage(Message $message)
    {
        DeleteChatMessageEvent::dispatch($message);

        $message->delete();

        return $message;
    }

    public function fetchMessages(Thread $thread)
    {
        $messages = Message::query()
            ->where('thread_id', $thread->id)
            ->with('user')->with('replyTo')
            ->orderBy('id', 'desc')
            ->paginate(30);

        return response()->json($messages);
    }

    public function loadMore(Thread $thread, $length, Request $request)
    {
        $messagesCount = Message::query()->where('thread_id', $thread->id)->count();
        $messages = Message::query()->where('thread_id', $thread->id)->with('user')
            ->orderBy('id', 'desc')
            ->skip($messagesCount - $length)
            ->take($length)
            ->get();

        if ($request->wantsJson()) {
            return $messages;
        }

        return $messages->reverse()->values();
    }

    public function leaveParticipant(Thread $thread)
    {
        $thread->removeParticipant(Auth::id());

        $message = new Message();
        $message->thread_id = $thread->id;
        $message->user_id = Auth::user()->id;
        $message->type = 200;
        $message->body = Auth::user()->fullName.' покинул беседу';
        $message->save();

        NewChatMessageEvent::dispatch($message);

        return Redirect::route('threads.index');
    }

    public function addFriendsToThread(Request $request, Thread $thread)
    {
        $user = User::query()->where('id', $request['userID'])->firstOrFail();
        $thread->addParticipant($user->id);

        $message = new Message();
        $message->thread_id = $thread->id;
        $message->user_id = Auth::user()->id;
        $message->type = 201;
        $message->body = Auth::user()->fullName.' добавил пользователя '.$user->fullName.' в беседу';
        $message->save();

        $thread->touch();

        NewChatMessageEvent::dispatch($message);
    }

    public function fetchFriends(Thread $thread)
    {
        $userId = Auth::id();
        $collectFriends = collect(Auth::user()->friends());

        return $collectFriends->reject(function ($friend) use ($thread, $userId) {
            return $thread->hasParticipant($friend->id) || $friend->id == $userId;
        });
    }

    public function getPeople(Thread $thread)
    {
        $user = User::query()->whereNot('id', Auth::id())->inRandomOrder()->first();

        $message = new Message();
        $message->thread_id = $thread->id;
        $message->user_id = $user->id;
        $message->type = 400;
        $message->body = null;
        $message->save();

        $message->load('user');

        NewChatMessageEvent::dispatch($message);

        return $message;
    }

    public function startMessage(User $user)
    {
        $thread = Thread::query()
            ->with('participants')
            ->whereRelation('participants', 'user_id', $user->id)
            ->whereRelation('participants', 'user_id', Auth::id())
            ->first();

        if (!$thread) {
            $thread = Thread::query()->create([
                'type' => 1,
                'image' => null,
            ]);

            Participant::query()->firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'admin' => 0,
                'muted' => 0,
            ]);

            Participant::query()->firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => Auth::id(),
                'admin' => 0,
                'muted' => 0,
            ]);
        }

        return Redirect::route('threads.show', $thread->id);
    }

}
