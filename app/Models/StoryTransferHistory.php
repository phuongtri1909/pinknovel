<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StoryTransferHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'story_title',
        'story_slug',
        'old_author_id',
        'old_author_name',
        'old_author_email',
        'new_author_id',
        'new_author_name',
        'new_author_email',
        'reason',
        'transfer_type',
        'transfer_metadata',
        'transferred_by',
        'transferred_by_name',
        'ip_address',
        'user_agent',
        'transferred_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'transfer_metadata' => 'array',
        'transferred_at' => 'datetime',
    ];

    // Constants
    const TYPE_SINGLE = 'single';
    const TYPE_BULK = 'bulk';

    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERTED = 'reverted';

    /**
     * Relationship with Story
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Relationship with Old Author
     */
    public function oldAuthor()
    {
        return $this->belongsTo(User::class, 'old_author_id');
    }

    /**
     * Relationship with New Author
     */
    public function newAuthor()
    {
        return $this->belongsTo(User::class, 'new_author_id');
    }

    /**
     * Relationship with Transfer Admin
     */
    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    /**
     * Get transfer type text
     */
    public function getTransferTypeTextAttribute()
    {
        return $this->transfer_type === self::TYPE_SINGLE ? 'Đơn lẻ' : 'Hàng loạt';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case self::STATUS_COMPLETED:
                return 'bg-gradient-success';
            case self::STATUS_FAILED:
                return 'bg-gradient-danger';
            case self::STATUS_REVERTED:
                return 'bg-gradient-warning';
            default:
                return 'bg-gradient-secondary';
        }
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_COMPLETED:
                return 'Hoàn thành';
            case self::STATUS_FAILED:
                return 'Thất bại';
            case self::STATUS_REVERTED:
                return 'Đã hoàn tác';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Get formatted transferred at
     */
    public function getTransferredAtFormattedAttribute()
    {
        return $this->transferred_at->format('d/m/Y H:i:s');
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->transferred_at->diffForHumans();
    }

    /**
     * Create transfer history record
     */
    public static function createRecord(array $data)
    {
        return self::create([
            'story_id' => $data['story_id'],
            'story_title' => $data['story_title'],
            'story_slug' => $data['story_slug'] ?? null,
            'old_author_id' => $data['old_author_id'],
            'old_author_name' => $data['old_author_name'],
            'old_author_email' => $data['old_author_email'],
            'new_author_id' => $data['new_author_id'],
            'new_author_name' => $data['new_author_name'],
            'new_author_email' => $data['new_author_email'],
            'reason' => $data['reason'],
            'transfer_type' => $data['transfer_type'] ?? self::TYPE_SINGLE,
            'transfer_metadata' => $data['transfer_metadata'] ?? null,
            'transferred_by' => $data['transferred_by'],
            'transferred_by_name' => $data['transferred_by_name'],
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'transferred_at' => $data['transferred_at'] ?? now(),
            'status' => $data['status'] ?? self::STATUS_COMPLETED,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Scope for completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed transfers
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for recent transfers
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('transferred_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific story
     */
    public function scopeForStory($query, $storyId)
    {
        return $query->where('story_id', $storyId);
    }

    /**
     * Scope for specific author (old or new)
     */
    public function scopeForAuthor($query, $authorId)
    {
        return $query->where('old_author_id', $authorId)
                    ->orWhere('new_author_id', $authorId);
    }
}
