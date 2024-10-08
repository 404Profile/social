<?php

namespace App\Traits;

use App\Models\Friend;
use App\Models\User;

trait HasFriendTrait
{
    /**
     * Handles adding friends.
     *
     * @param  int  $userRequestedId
     * @return int|string
     */

    public function addFriend(int $userRequestedId): int|string
    {
        if ($this->id === $userRequestedId) {
            return 0;
        }

        if ($this->isFriendsWith($userRequestedId) === 1) {
            return "Уже в друзьях";
        }

        if ($this->hasPendingFriendRequestSentTo($userRequestedId) === 1) {
            return "Уже отправлен запрос в друзья";
        }

        if ($this->hasPendingFriendRequestFrom($userRequestedId) === 1) {
            return $this->acceptFriend($userRequestedId);
        }

        $friendship = Friend::create([
            'requester' => $this->id,
            'user_requested' => $userRequestedId
        ]);

        if ($friendship) {
            return 1;
        }

        return 0;
    }

    /**
     * Checks if the user is friends with the specified user ID
     *
     * @param  int  $id
     * @return int
     */
    public function isFriendsWith(int $id): int
    {
        if (in_array($id, $this->friendsIds())) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the ID of friends
     *
     * @return array
     */
    public function friendsIds(): array
    {
        return collect($this->friends())->pluck('id')->toArray();
    }

    /**
     * Returns friends
     *
     * @return array
     */
    public function friends(): array
    {
        $friends = array();

        $f1 = Friend::query()
            ->where('status', 1)
            ->where('requester', $this->id)
            ->get();

        foreach ($f1 as $friendship):
            $friends[] = User::query()->find($friendship->user_requested);
        endforeach;

        $friends2 = array();
        $f2 = Friend::query()
            ->where('status', 1)
            ->where('user_requested', $this->id)
            ->get();

        foreach ($f2 as $friendship):
            $friends2[] = User::query()->find($friendship->requester);
        endforeach;

        return array_merge($friends, $friends2);
    }

    /**
     * Check if there is a pending friend request sent to a user.
     *
     * @param $user_id
     * @return int
     */
    public function hasPendingFriendRequestSentTo($user_id): int
    {
        if (in_array($user_id, $this->pendingFriendRequestsSentIds())) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get an array of IDs for pending friend requests sent by the user.
     *
     * @return array
     */
    public function pendingFriendRequestsSentIds(): array
    {
        return collect($this->pendingFriendRequestsSent())->pluck('id')->toArray();
    }

    /**
     * Get an array of users with pending friend requests sent by the user.
     *
     * @return array
     */
    public function pendingFriendRequestsSent(): array
    {
        $users = array();

        $friendships = Friend::query()
            ->where('status', 0)
            ->where('requester', $this->id)
            ->get();

        foreach ($friendships as $friendship):
            $users[] = User::query()->find($friendship->user_requested);
        endforeach;

        return $users;
    }

    /**
     * Check if there is a pending friend request from a user.
     *
     * @param $user_id
     * @return int
     */
    public function hasPendingFriendRequestFrom($user_id): int
    {
        if (in_array($user_id, $this->pendingFriendRequestsIds())) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get an array of IDs for pending friend requests received by the user.
     *
     * @return array
     */
    public function pendingFriendRequestsIds(): array
    {
        return collect($this->pendingFriendRequests())->pluck('id')->toArray();
    }

    /**
     * Get an array of users with pending friend requests received by the user.
     *
     * @return array
     */
    public function pendingFriendRequests(): array
    {
        $users = array();

        $friendships = Friend::query()
            ->where('status', 0)
            ->where('user_requested', $this->id)
            ->get();

        foreach ($friendships as $friendship):
            $users[] = User::query()->find($friendship->requester);
        endforeach;

        return $users;
    }

    /**
     * Accept a friend request from a user.
     *
     * @param $requester
     * @return int
     */
    public function acceptFriend($requester): int
    {
        if ($this->hasPendingFriendRequestFrom($requester) === 0) {
            return 0;
        }

        $friendship = Friend::query()
            ->where('requester', $requester)
            ->where('user_requested', $this->id)
            ->first();

        if ($friendship) {
            $friendship->update([
                'status' => 1
            ]);

            return 1;
        }

        return 0;
    }

    /**
     * Deny a friend request from a user.
     *
     * @param $requester
     * @return int
     */
    public function denyFriend($requester): int
    {
        if ($this->hasPendingFriendRequestFrom($requester) === 0) {
            return 0;
        }

        $friendship = Friend::query()
            ->where('requester', $requester)
            ->where('user_requested', $this->id)
            ->first();

        if ($friendship) {
            $friendship->delete();
            return 1;
        }

        return 0;
    }

    /**
     * Delete a friend connection with a user.
     *
     * @return int|void
     */
    public function deleteFriend($userRequestedId)
    {
        if ($this->id === $userRequestedId) {
            return 0;
        }

        if ($this->isFriendsWith($userRequestedId) === 1) {
            $friendship1 = Friend::query()
                ->where('requester', $userRequestedId)
                ->where('user_requested', $this->id)
                ->first();

            if ($friendship1) {
                $friendship1->delete();
            }

            $friendship2 = Friend::query()
                ->where('user_requested', $userRequestedId)
                ->where('requester', $this->id)
                ->first();

            if ($friendship2) {
                $friendship2->delete();
            }
        }
    }

    /**
     * Revoke a friend request.
     *
     * @return int|void
     */
    public function canselFriendRequest($userRequestedId)
    {
        if ($this->id === $userRequestedId) {
            return 0;
        }

        if ($this->isFriendsWith($userRequestedId) === 0) {
            $friendship1 = Friend::query()
                ->where('requester', $userRequestedId)
                ->where('user_requested', $this->id)
                ->first();

            if ($friendship1) {
                $friendship1->delete();
            }

            $friendship2 = Friend::query()
                ->where('user_requested', $userRequestedId)
                ->where('requester', $this->id)
                ->first();

            if ($friendship2) {
                $friendship2->delete();
            }
        }
    }
}
