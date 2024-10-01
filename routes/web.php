<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
});
