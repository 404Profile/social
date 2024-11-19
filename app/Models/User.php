<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasFriendTrait;
use App\Traits\HasLikeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasFriendTrait;
    use HasLikeTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'private',
        'age',
        'gender',
        'about',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'fullName',
        'location',
    ];

    public function hasRole($role): bool
    {
        return (bool) $this->roles()->where('title', $role)->first();
    }

    public function roles(): HasOne
    {
        return $this->hasOne(Role::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->roles()->where('title', 'Администратор')->first();
    }

    public function isUser(): bool
    {
        return (bool) $this->roles()->where('title', 'Пользователь')->first();
    }

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->surname;
    }

    public function getLocationAttribute()
    {
        return $this->country.' '.$this->city;
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'owner_user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function cities()
    {
        return $this->hasOne(City::class);
    }

    public function countries()
    {
        return $this->hasOne(Country::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
