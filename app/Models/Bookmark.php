<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'story_id',
        'notification_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'notification_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the story that is bookmarked.
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Toggle bookmark for a story.
     *
     * @param int $userId
     * @param int $storyId
     * @return array
     */
    public static function toggleBookmark($userId, $storyId)
    {
        $bookmark = self::where('user_id', $userId)
            ->where('story_id', $storyId)
            ->first();
            
        if ($bookmark) {
            $bookmark->delete();
            return [
                'status' => 'removed',
                'message' => 'Đã xóa truyện khỏi danh sách theo dõi'
            ];
        } else {
            self::create([
                'user_id' => $userId,
                'story_id' => $storyId,
                'notification_enabled' => true
            ]);
            return [
                'status' => 'added',
                'message' => 'Đã thêm truyện vào danh sách theo dõi'
            ];
        }
    }
    
    /**
     * Check if a user has bookmarked a story.
     *
     * @param int $userId
     * @param int $storyId
     * @return bool
     */
    public static function isBookmarked($userId, $storyId)
    {
        return self::where('user_id', $userId)
            ->where('story_id', $storyId)
            ->exists();
    }
    
    /**
     * Toggle notification setting for a bookmark.
     *
     * @param int $id
     * @return array
     */
    public static function toggleNotification($id)
    {
        $bookmark = self::findOrFail($id);
        $bookmark->notification_enabled = !$bookmark->notification_enabled;
        $bookmark->save();
        
        return [
            'status' => 'success',
            'notification_enabled' => $bookmark->notification_enabled,
            'message' => $bookmark->notification_enabled 
                ? 'Đã bật thông báo cho truyện này' 
                : 'Đã tắt thông báo cho truyện này'
        ];
    }
}