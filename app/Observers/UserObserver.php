<?php

namespace App\Observers;

use App\Models\Participant;
use App\Models\Thread;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $thread = Thread::create([
            'title' => $user->fullName,
            'type' => 3,
            'image' => null,
        ]);

        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'admin' => 0,
            'muted' => 0,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
