<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request as RequestSearch;
use Inertia\Inertia;

class FriendController extends Controller
{
    public function allUsers()
    {
        $users = User::query()->whereNot('id', Auth::id())->paginate(100);

        return Inertia::render('User/Friends/Users', [
            'users' => $users,
        ]);
    }

    public function index(User $user)
    {
        return Inertia::render('User/Friends/Index', [
            'user' => $user,
            'friends' => $user->friends(),
            'requests' => $user->pendingFriendRequests(),
        ]);
    }

    public function following(User $user)
    {
        return Inertia::render('User/Friends/Following', [
            'user' => $user,
            'following' => $user->pendingFriendRequestsSent(),
        ]);
    }

    public function followers(User $user)
    {
        return Inertia::render('User/Friends/Followers', [
            'user' => $user,
            'followers' => $user->pendingFriendRequests(),
        ]);
    }

    public function friendRequests()
    {
        return Inertia::render('User/Friends/FriendRequests', [
            'requests' => auth()->user()->pendingFriendRequests(),
        ]);
    }

    public function store(User $user)
    {
        if (!$user) {
            return Redirect::back()->with('error', 'Данный пользователь не найден');
        }

        Auth::user()->addFriend($user->id);
        return Redirect::back();
    }

    public function update(User $user)
    {
        if (!$user) {
            return Redirect::back()->with('error', 'Данный пользователь не найден');
        }

        Auth::user()->acceptFriend($user->id);
        return Redirect::back();
    }

    public function destroy(User $user)
    {
        if (!$user) {
            return Redirect::back()->with('error', 'Данный пользователь не найден');
        }

        Auth::user()->deleteFriend($user->id);
        return Redirect::back();
    }

    public function canselRequest(User $user)
    {
        if (!$user) {
            return Redirect::back()->with('error', 'Данный пользователь не найден');
        }

        Auth::user()->canselFriendRequest($user->id);
        return Redirect::back();
    }

    public function deny(User $user)
    {
        if (!$user) {
            return Redirect::back()->with('error', 'Данный пользователь не найден');
        }

        Auth::user()->denyFriend($user->id);
        return Redirect::back();
    }

    public function searchFriends(Request $request)
    {
        $users = User::notAuth()->whereNotIn('id', auth()->user()->friendsIds())
            ->when(RequestSearch::input('search'), function ($query, $search) {
                $query->where('email', 'LIKE', "%{$search}%");
            })->paginate(10)->withQueryString();

        return Inertia::render('User/Friends/SearchFriends', [
            'users' => $users,
            'filters' => RequestSearch::all('search'),
        ]);
    }

}
