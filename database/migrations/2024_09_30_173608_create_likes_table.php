<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('like')->default(0);
            $table->string('likeable_type');
            $table->foreignId('post_id')->nullable()->references('id')->on('posts')->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->references('id')->on('media')->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->references('id')->on('comments')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
