<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'filename',
        'type',
        'size',
    ];

    protected $appends = [
        'fullUrl',
        'liked',
        'isUserLikedMedia',
    ];

    public function getLikedAttribute()
    {
        return $this->likes()->where('like', 1)
            ->where('media_id', $this->id)
            ->where('likeable_type', '=', 'media')
            ->count();
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'media_id');
    }

    public function getIsUserLikedMediaAttribute()
    {
        return (bool) $this->likes()->where('like', 1)
            ->where('media_id', $this->id)
            ->where('likeable_type', '=', 'media')
            ->where('user_id', Auth::id())
            ->count();
    }

    public function getFullUrlAttribute()
    {
        return url($this->filepath);
    }

    public function getFilePathAttribute()
    {
        return 'storage/media/'.$this->created_at->format('Y').'/'.$this->created_at->format('m').'/'.$this->filename;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAllMedia($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('created_at');
    }
}
