<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Friend>
 */
class FriendFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requester = User::query()->inRandomOrder()->first()->id;
        $user_requested = User::query()->whereNot('id', $requester)->inRandomOrder()->first()->id;

        return [
            'requester' => $requester,
            'user_requested' => $user_requested,
            'status' => fake()->boolean(),
        ];
    }
}
