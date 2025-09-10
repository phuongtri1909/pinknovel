<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'current_page',
        'referer',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOnline($query)
    {
        return $query->where('last_activity', '>=', now()->subMinutes(5));
    }

    public function scopeGuests($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeUsers($query)
    {
        return $query->whereNotNull('user_id');
    }
}
