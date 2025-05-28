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
        'recently_read',
        'coins'
    ];

    /**
     * Get all chapter purchases made by this user
     */
    public function chapterPurchases()
    {
        return $this->hasMany(ChapterPurchase::class);
    }

    /**
     * Get all story purchases made by this user
     */
    public function storyPurchases()
    {
        return $this->hasMany(StoryPurchase::class);
    }

    /**
     * Get all deposits made by this user
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Get all bookmarks created by this user
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

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

    /**
     * Get total amount spent on chapters
     */
    public function getTotalChapterSpendingAttribute()
    {
        return $this->chapterPurchases()->sum('amount_paid');
    }

    /**
     * Get total amount spent on stories
     */
    public function getTotalStorySpendingAttribute()
    {
        return $this->storyPurchases()->sum('amount_paid');
    }

    /**
     * Get total amount deposited
     */
    public function getTotalDepositsAttribute()
    {
        return $this->deposits()->where('status', 'approved')->sum('coins');
    }

    /**
     * Get total revenue for author (from chapters and stories they've authored)
     */
    public function getAuthorRevenueAttribute()
    {
        // Get stories authored by this user
        $storyIds = Story::where('user_id', $this->id)->pluck('id');
        
        // Calculate revenue from story purchases
        $storyRevenue = StoryPurchase::whereIn('story_id', $storyIds)->sum('amount_paid');
        
        // Calculate revenue from chapter purchases
        $chapterRevenue = ChapterPurchase::whereHas('chapter', function($query) {
            $query->whereHas('story', function($query) {
                $query->where('user_id', $this->id);
            });
        })->sum('amount_paid');
        
        return $storyRevenue + $chapterRevenue;
    }

    /**
     * Get coin transactions for this user
     */
    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class);
    }

    /**
     * Get coin transactions administered by this user
     */
    public function administeredCoinTransactions()
    {
        return $this->hasMany(CoinTransaction::class, 'admin_id');
    }

    /**
     * Get withdrawal requests made by this user
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get withdrawal requests processed by this user (as admin)
     */
    public function processedWithdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by');
    }
}
