<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StoryFeatured extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'user_id',
        'type',
        'featured_order',
        'price_paid',
        'duration_days',
        'featured_at',
        'featured_until',
        'is_active',
        'note',
    ];

    protected $casts = [
        'featured_at' => 'datetime',
        'featured_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    const TYPE_ADMIN = 'admin';
    const TYPE_AUTHOR = 'author';

    /**
     * Relationship với Story
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Lấy các đề cử đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('featured_until', '>', now());
    }

    /**
     * Scope: Lấy đề cử của admin
     */
    public function scopeAdmin($query)
    {
        return $query->where('type', self::TYPE_ADMIN);
    }

    /**
     * Scope: Lấy đề cử của author
     */
    public function scopeAuthor($query)
    {
        return $query->where('type', self::TYPE_AUTHOR);
    }

    /**
     * Kiểm tra xem đề cử có còn hiệu lực không
     */
    public function isCurrentlyActive()
    {
        return $this->is_active && $this->featured_until->isFuture();
    }

    /**
     * Lấy số ngày còn lại
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->isCurrentlyActive()) {
            return 0;
        }
        
        return now()->diffInDays($this->featured_until, false);
    }

    /**
     * Tạo đề cử mới
     */
    public static function createFeatured($storyId, $userId, $type, $durationDays = 7, $pricePaid = 0, $featuredOrder = null, $note = null)
    {
        $featuredAt = now();
        $featuredUntil = $featuredAt->copy()->addDays($durationDays);

        return self::create([
            'story_id' => $storyId,
            'user_id' => $userId,
            'type' => $type,
            'featured_order' => $featuredOrder,
            'price_paid' => $pricePaid,
            'duration_days' => $durationDays,
            'featured_at' => $featuredAt,
            'featured_until' => $featuredUntil,
            'is_active' => true,
            'note' => $note,
        ]);
    }

    /**
     * Hủy đề cử
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
