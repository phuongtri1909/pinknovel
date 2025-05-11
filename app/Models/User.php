<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'active',
        'key_active',
        'key_reset_password',
        'reset_password_at',
        'ban_login',
        'ban_comment',
        'ban_rate',
        'ban_read',
        'ip_address',
        'rating',
        'recently_read'
    ];
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function isBanLogin(): bool
    {
        return $this->ban_login;
    }

    public function isBanComment(): bool
    {
        return $this->ban_comment;
    }

    public function isBanRate(): bool
    {
        return $this->ban_rate;
    }

    public function isBanRead(): bool
    {
        return $this->ban_read;
    }


    public function banned_ips()
    {
        return $this->hasMany(Banned_ip::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the bookmarks for the user.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // Thêm vào model User.php
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Get the author applications for the user
     */
    public function authorApplications()
    {
        return $this->hasMany(AuthorApplication::class);
    }

    /**
     * Check if the user has a pending author application
     */
    public function hasPendingAuthorApplication()
    {
        return $this->authorApplications()->where('status', 'pending')->exists();
    }

    /**
     * Check if the user has an approved author application
     */
    public function hasApprovedAuthorApplication()
    {
        return $this->authorApplications()->where('status', 'approved')->exists();
    }

    /**
     * Get the latest author application
     */
    public function latestAuthorApplication()
    {
        return $this->authorApplications()->latest()->first();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'recently_read' => 'array'
    ];
}
