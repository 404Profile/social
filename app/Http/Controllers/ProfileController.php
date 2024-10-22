<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function profile(User $user)
    {
        $user->load([
            'posts' => function ($query) {
                $query->orderByDesc('id');
            }, 'posts.media.comments', 'posts.likes', 'posts.comments'
        ]);

        $media = Media::query()->with('post', 'comments')->where('user_id', $user->id)
            ->whereRelation('post', 'owner_user_id', $user->id)
            ->orderByDesc('id')->take(4)->get();

        $counterMedia = Media::query()->with('post')->where('user_id', $user->id)
            ->whereRelation('post', 'owner_user_id', $user->id)->count();

        $counterPosts = count($user->posts);

        return Inertia::render('User/Profile/Show', [
            'user' => $user,
            'media' => $media,
            'counterMedia' => $counterMedia,
            'counterPosts' => $counterPosts,
            'friendsCount' => count($user->friends()),
            'followersCount' => count($user->pendingFriendRequests()),
            'isFriendsWith' => Auth::user()->isFriendsWith($user->id),
            'friendRequestSentTo' => Auth::user()->hasPendingFriendRequestSentTo($user->id),
            'friendRequestReceivedFrom' => Auth::user()->hasPendingFriendRequestFrom($user->id),
            'followingCount' => count($user->pendingFriendRequestsSent()),
        ]);
    }
}
