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
        'password_hint',
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

    /**
     * Mã hóa mật khẩu chương sử dụng key riêng
     */
    private function encryptPassword($password)
    {
        $key = config('chapter.password_key');
        if (empty($key)) {
            throw new \Exception('CHAPTER_PASSWORD_KEY chưa được cấu hình trong .env');
        }
        
        $cipher = config('chapter.cipher', 'AES-256-CBC');
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($password, $cipher, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Giải mã mật khẩu chương
     */
    private function decryptPassword($encrypted)
    {
        $key = config('chapter.password_key');
        if (empty($key)) {
            throw new \Exception('CHAPTER_PASSWORD_KEY chưa được cấu hình trong .env');
        }
        
        $cipher = config('chapter.cipher', 'AES-256-CBC');
        
        try {
            $data = base64_decode($encrypted);
            if ($data === false) {
                // Có thể là mật khẩu cũ chưa được encrypt (plain text)
                return $encrypted;
            }
            
            $ivLength = openssl_cipher_iv_length($cipher);
            if (strlen($data) < $ivLength) {
                // Có thể là mật khẩu cũ chưa được encrypt (plain text)
                return $encrypted;
            }
            
            $iv = substr($data, 0, $ivLength);
            $encryptedData = substr($data, $ivLength);
            $decrypted = openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
            
            if ($decrypted === false) {
                // Giải mã thất bại, có thể là mật khẩu cũ (plain text)
                return $encrypted;
            }
            
            return $decrypted;
        } catch (\Exception $e) {
            // Giải mã lỗi, trả về nguyên bản (có thể là plain text)
            return $encrypted;
        }
    }
    
    public function checkPassword($password)
    {
        if (empty($this->password)) {
            return false;
        }
        
        try {
            // Giải mã mật khẩu đã lưu và so sánh
            $decryptedPassword = $this->decryptPassword($this->password);
            return $password === $decryptedPassword;
        } catch (\Exception $e) {
            // Nếu giải mã lỗi (có thể là mật khẩu cũ chưa được encrypt), fallback về so sánh trực tiếp
            return $password === $this->password;
        }
    }
    
    /**
     * Lấy mật khẩu đã giải mã
     */
    public function getDecryptedPassword()
    {
        if (empty($this->password)) {
            return null;
        }
        
        try {
            return $this->decryptPassword($this->password);
        } catch (\Exception $e) {
            // Nếu giải mã lỗi (có thể là mật khẩu cũ chưa được encrypt), trả về nguyên bản
            return $this->password;
        }
    }

    public function purchases()
    {
        return $this->hasMany(ChapterPurchase::class);
    }

    /**
     * Check if a user has purchased this chapter
     */
    public function isPurchasedBy($userId)
    {
        if ($this->is_free) {
            return true;
        }
        
        // Check if the user has purchased the individual chapter
        if ($this->purchases()->where('user_id', $userId)->exists()) {
            return true;
        }
        
        // Check if the user has purchased the story combo
        return $this->story->purchases()->where('user_id', $userId)->exists();
    }
}
