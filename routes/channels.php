<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    if (Auth::check()) {
        return $user;
    }
});

//Broadcast::channel('thread.{threadId}', function ($user, $threadId) {
//    if (Auth::check()) {
//        return $user;
//    }
//});

Broadcast::channel('chat.{id}', function ($user, $id) {
    if (Auth::check()) {
        return $user;
    }
});
