<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'media_id',
        'body'
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
        'timeAgo',
        'isUserLikedComment'
    ];

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getLikedAttribute()
    {
        return $this->likes()->where('like', 1)
            ->where('comment_id', $this->id)
            ->where('likeable_type', '=', 'comment')
            ->count();
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'comment_id');
    }

    public function getIsUserLikedCommentAttribute()
    {
        return (bool) $this->likes()->where('like', 1)
            ->where('comment_id', $this->id)
            ->where('likeable_type', '=', 'comment')
            ->where('user_id', Auth::id())
            ->count();
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

}
