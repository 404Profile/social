<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    use HasFactory;

    const DefaultPermissions = [
        'admin' => 0,
        'muted' => false,
    ];

    const AdminPermissions = [
        'admin' => 1,
        'add_participants' => true,
        'muted' => false,
    ];

    const CreatorPermissions = [
        'admin' => 2,
        'add_participants' => true,
        'muted' => false,
    ];

    protected $fillable = [
        'thread_id',
        'user_id',
        'admin',
        'muted',
        'last_read',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('admin', '>=', 1);
    }

    public function scopeCreator(Builder $query): Builder
    {
        return $query->where('admin', '=', 2);
    }
}
