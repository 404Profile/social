<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $likeableType = fake()->randomElement(['post', 'media', 'comment']);
        $likeableId = null;

        switch ($likeableType) {
            case 'post':
                $likeableId = Post::query()->inRandomOrder()->first()->id;
                break;
            case 'media':
                $likeableId = Media::query()->inRandomOrder()->first()->id;
                break;
            case 'comment':
                $likeableId = Comment::query()->inRandomOrder()->first()->id;
                break;
        }

        return [
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'like' => 1,
            'likeable_type' => $likeableType,
            'post_id' => $likeableType === 'post' ? $likeableId : null,
            'media_id' => $likeableType === 'media' ? $likeableId : null,
            'comment_id' => $likeableType === 'comment' ? $likeableId : null,
        ];
    }
}
