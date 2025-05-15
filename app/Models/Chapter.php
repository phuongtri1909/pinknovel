<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'number',
        'views',
        'status',
        'story_id',
        'user_id',
        'link_aff',
        'price',
        'password',
        'is_free',
        'scheduled_publish_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'scheduled_publish_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function checkPassword($password)
    {
        if (empty($this->password)) {
            return false;
        }
        return Hash::check($password, $this->password);
    }
}
