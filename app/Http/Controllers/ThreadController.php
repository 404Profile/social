<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewChatMessage;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Thread;
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
            ->with('participants')
            ->whereRelation('participants', 'user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        return Inertia::render('User/Threads/Index', [
            'threads' => $threads,
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
        ])->validate();

        $thread = new Thread();
        $thread->type = '2';
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
        $message->type = '101';
        $message->body = Auth::user()->fullName.' создал чат.';
        $message->save();

        return Redirect::route('threads.show', $thread->id)->with('success', 'Чат успешно создан.');
    }

    public function show(Thread $thread)
    {
//        $thread->load('messages.user');

        return Inertia::render('User/Threads/Show', [
            'thread' => $thread,
        ]);
    }

    public function message(Request $request, Thread $thread)
    {
        Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:65535'],
        ])->validate();

        $message = $thread->messages()->create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'type' => '0',
            'body' => $request['body'],
        ]);

        $thread->touch();

        $message->load('thread')->load('user');

        SendNewChatMessage::dispatch($message);

        return $message;
    }

    public function fetchMessages(Thread $thread)
    {
        $messages = Message::query()
            ->where('thread_id', $thread->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(10);

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

}
