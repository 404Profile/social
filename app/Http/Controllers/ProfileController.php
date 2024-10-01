<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function profile(User $user)
    {
        $user->load([
            'posts' => function ($query) {
                $query->orderByDesc('created_at');
            }, 'posts.media.comments', 'posts.likes', 'posts.comments'
        ]);

        $media = Media::query()->with('post', 'comments')->where('user_id', $user->id)
            ->whereRelation('post', 'parent_id', $user->id)
            ->orderByDesc('id')->take(4)->get();

        $counterMedia = Media::query()->with('post')->where('user_id', $user->id)
            ->whereRelation('post', 'parent_id', $user->id)->count();

        $counterPosts = count($user->posts);

        return Inertia::render('User/Profile/Show', [
            'user' => $user,
            'media' => $media,
            'counterMedia' => $counterMedia,
            'counterPosts' => $counterPosts,
        ]);
    }
}
