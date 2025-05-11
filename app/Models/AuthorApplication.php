<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facebook_link',
        'telegram_link',
        'other_platform',
        'other_platform_link',
        'introduction',
        'status',
        'admin_note',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user associated with the application
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the application is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the application is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the application is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
