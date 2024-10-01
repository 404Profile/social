<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        'body',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'liked',
        'isUserLikedPost',
        'timeAgo'
    ];

    public function getLikedAttribute()
    {
        return $this->likes()->where('like', 1)
            ->where('post_id', $this->id)
            ->where('likeable_type', '=', 'post')
            ->count();
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    public function getIsUserLikedPostAttribute()
    {
        return (bool) $this->likes()->where('like', 1)
            ->where('post_id', $this->id)
            ->where('likeable_type', '=', 'post')
            ->where('user_id', Auth::id())
            ->count();
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('created_at');
    }
}
