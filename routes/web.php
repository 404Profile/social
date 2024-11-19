<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile/{user}', [ProfileController::class, 'profile'])
        ->where('user', '[0-9]+')
        ->name('userProfile');

    Route::post('/storePost', [PostController::class, 'store'])->name('storePost');

    Route::post('/liked', [LikeController::class, 'store'])->name('liked');
    Route::post('/getMediaOnProfile', [MediaController::class, 'getMedia'])->name('getMedia');
    Route::post('/createComment', [CommentController::class, 'store'])->name('createComment');
    Route::delete('/deleteComment/{comment}', [CommentController::class, 'destroy'])->name('deleteComment');

    Route::prefix('friends')->name('friends.')->controller(FriendController::class)->group(function () {
        Route::get('{user}', 'index')->name('index');
        Route::get('{user}/following', 'following')->name('following');
        Route::get('{user}/followers', 'followers')->name('followers');
        Route::get('requests', 'friendRequests')->name('friendRequests');
        Route::post('sendFriendRequest/{user}', 'store')->name('store');
        Route::patch('updateFriendRequest/{user}', 'update')->name('update');
        Route::get('denyFriendRequest/{user}', 'deny')->name('deny');
        Route::delete('deleteFriendRequest/{user}', 'destroy')->name('destroy');
        Route::delete('/canselRequest/{user}', 'canselRequest')->name('canselRequest');
    });

    Route::get('/allUsers', [FriendController::class, 'allUsers'])->name('friends.allUsers');

    Route::prefix('threads')->name('threads.')->controller(ThreadController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('{thread:id}', 'show')->name('show');
        Route::put('{thread:id}', 'update')->name('update');
        Route::post('store', 'store')->name('store');
        Route::post('{thread:id}/message', 'message')->name('message');
        Route::put('{thread:id}/message', 'updateMessage')->name('updateMessage');
        Route::delete('message/{message:id}', 'deleteMessage')->name('deleteMessage');
        Route::get('{thread:id}/fetchMessages', 'fetchMessages')->name('fetchMessages');
        Route::get('{thread:id}/{length}/loadMore', 'loadMore')->name('loadMore');
        Route::post('{thread:id}/leaveParticipant', 'leaveParticipant')->name('leaveParticipant');
        Route::post('{thread:id}/addFriendsToThread', 'addFriendsToThread')->name('addFriendsToThread');

        Route::get('{thread:id}/fetchImages', 'fetchImages')->name('fetchImages');
        Route::get('{thread:id}/fetchDocuments', 'fetchDocuments')->name('fetchDocuments');
        Route::get('{thread:id}/fetchAudios', 'fetchAudios')->name('fetchAudios');
        Route::get('{thread:id}/fetchVideos', 'fetchVideos')->name('fetchVideos');
        Route::get('{thread:id}/loadMoreFetchImages/{page}', 'loadMoreFetchImages')
            ->name('loadMoreFetchImages');
        Route::get('{thread:id}/loadMoreFetchDocuments/{page}', 'loadMoreFetchDocuments')
            ->name('loadMoreFetchDocuments');
        Route::get('{thread:id}/loadMoreFetchAudios/{page}', 'loadMoreFetchAudios')
            ->name('loadMoreFetchAudios');
        Route::get('{thread:id}/loadMoreFetchVideos/{page}', 'loadMoreFetchVideos')
            ->name('loadMoreFetchVideos');
        Route::get('{thread:id}/fetchFriends', 'fetchFriends')->name('fetchFriends');
        Route::get('{thread:id}/getPeople', 'getPeople')->name('getPeople');
    });

    Route::post('startMessage/{user}', [ThreadController::class, 'startMessage'])->name('startMessage');
});
