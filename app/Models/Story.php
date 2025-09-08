<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'status',
        'cover',
        'cover_medium',
        'cover_thumbnail',
        'completed',
        'link_aff',
        'story_type',
        'author_name',
        'is_18_plus',
        'combo_price',
        'has_combo',
        'translator_name',
        'is_monopoly',
        'submitted_at',
        'reviewed_at',
        'review_note',
        'admin_note',
        'is_featured',
        'featured_order',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';


    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }


    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePopular($query)
    {
        return $query->withCount('chapters')->orderByDesc('chapters_count');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getTotalViewsAttribute()
    {
        return $this->chapters->sum('views');
    }

    public function getAverageViewsAttribute()
    {
        return $this->chapters_count > 0 ?
            $this->total_views / $this->chapters_count : 0;
    }

    public function latestChapter()
    {
        return $this->hasOne(Chapter::class)
            ->where('status', self::STATUS_PUBLISHED)
            ->orderByDesc('number');
    }
    /**
     * Get the bookmarks for the story.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function scopeOriginal($query)
    {
        return $query->where('story_type', 'original');
    }

    /**
     * Get the edit requests for the story
     */
    public function editRequests()
    {
        return $this->hasMany(StoryEditRequest::class);
    }

    /**
     * Check if the story has a pending edit request
     */
    public function hasPendingEditRequest()
    {
        return $this->editRequests()->where('status', 'pending')->exists();
    }

    /**
     * Get the latest pending edit request for the story
     */
    public function latestPendingEditRequest()
    {
        return $this->editRequests()->where('status', 'pending')->latest()->first();
    }

    /**
     * Trạng thái hoàn thành để tạo combo
     */
    public function getCanCreateComboAttribute()
    {
        return $this->completed && $this->chapters()->where('status', 'published')->count() > 0;
    }

    /**
     * Check if the story has a combo
     */
    public function hasCombo()
    {
        return $this->has_combo;
    }

    /**
     * Get the total chapter price
     */
    public function getTotalChapterPriceAttribute()
    {
        return $this->chapters()->where('status', 'published')->sum('price');
    }

    /**
     * Get discount percentage for combo
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->has_combo || $this->combo_price <= 0 || $this->total_chapter_price <= 0) {
            return 0;
        }

        $discount = (($this->total_chapter_price - $this->combo_price) / $this->total_chapter_price) * 100;
        return round($discount);
    }

    public function purchases()
    {
        return $this->hasMany(StoryPurchase::class);
    }

    /**
     * Check if a user has purchased this story combo
     */
    public function isPurchasedBy($userId)
    {
        return $this->purchases()->where('user_id', $userId)->exists();
    }

    public function storyPurchases()
    {
        return $this->hasMany(StoryPurchase::class);
    }

    /**
     * Relationship với StoryFeatured
     */
    public function featuredRecords()
    {
        return $this->hasMany(StoryFeatured::class);
    }

    /**
     * Lấy đề cử admin đang hoạt động
     */
    public function activeAdminFeatured()
    {
        return $this->hasOne(StoryFeatured::class)
                    ->where('type', StoryFeatured::TYPE_ADMIN)
                    ->active();
    }

    /**
     * Lấy đề cử author đang hoạt động
     */
    public function activeAuthorFeatured()
    {
        return $this->hasOne(StoryFeatured::class)
                    ->where('type', StoryFeatured::TYPE_AUTHOR)
                    ->active();
    }

    public function chapterPurchases()
    {
        return $this->hasManyThrough(
            ChapterPurchase::class,
            Chapter::class,
            'story_id',
            'chapter_id',
            'id',
            'id'
        );
    }


    public function getIsFeaturedAttribute($value)
    {
        return (bool) $value;
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->orderBy('featured_order');
    }

    public function scopeNotFeatured($query)
    {
        return $query->where('is_featured', false);
    }

    public static function getNextFeaturedOrder()
    {
        return self::where('is_featured', true)->max('featured_order') + 1;
    }

    /**
     * Check if story is currently featured by admin
     */
    public function isCurrentlyAdminFeatured()
    {
        return $this->activeAdminFeatured()->exists();
    }

    /**
     * Check if story is currently featured by author
     */
    public function isCurrentlyAuthorFeatured()
    {
        return $this->activeAuthorFeatured()->exists();
    }

    /**
     * Check if story is featured (admin or author)
     */
    public function isAnyFeatured()
    {
        return $this->is_featured || $this->isCurrentlyAdminFeatured() || $this->isCurrentlyAuthorFeatured();
    }

    /**
     * Get featured status text
     */
    public function getFeaturedStatusTextAttribute()
    {
        if ($this->is_featured || $this->isCurrentlyAdminFeatured()) {
            return 'Admin đề cử';
        } elseif ($this->isCurrentlyAuthorFeatured()) {
            return 'Tác giả đề cử';
        }
        return 'Thường';
    }

    /**
     * Get featured badge with priority
     */
    public function getFeaturedBadgeAttribute()
    {
        if ($this->is_featured || $this->isCurrentlyAdminFeatured()) {
            $adminFeatured = $this->activeAdminFeatured;
            $order = $adminFeatured ? $adminFeatured->featured_order : $this->featured_order;
            return '<span class="badge bg-gradient-warning">Admin đề cử #' . $order . '</span>';
        } elseif ($this->isCurrentlyAuthorFeatured()) {
            $authorFeatured = $this->activeAuthorFeatured;
            $daysLeft = $authorFeatured ? $authorFeatured->days_remaining : 0;
            return '<span class="badge bg-gradient-info">Tác giả đề cử (' . $daysLeft . ' ngày)</span>';
        }
        return '';
    }

    /**
     * Get current active featured record
     */
    public function getCurrentFeaturedAttribute()
    {
        // Ưu tiên admin featured
        if ($this->isCurrentlyAdminFeatured()) {
            return $this->activeAdminFeatured;
        } elseif ($this->isCurrentlyAuthorFeatured()) {
            return $this->activeAuthorFeatured;
        }
        return null;
    }

    protected $with = ['categories'];
}
