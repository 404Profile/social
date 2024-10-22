<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Friend;
use App\Models\Like;
use App\Models\Media;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'surname' => 'Admin',
            'email' => 'admin@admin.com',
            'age' => 20,
            'gender' => 'M',
            'role_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'a',
            'surname' => 'a',
            'email' => 'a@a.com',
            'age' => 20,
            'gender' => 'M',
        ]);

        User::factory(30)->create();
        Friend::factory(30)->create();
        Post::factory(30)->create();
        Media::factory(10)->create();
        Comment::factory(30)->create();
        Like::factory(50)->create();
        Thread::factory(10)->create();
        Participant::factory(10)->create();
        Message::factory(50)->create();
    }
}
