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
    ];


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
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
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
            ->where('status', 'published')
            ->orderByDesc('number');
    }

    protected $with = ['categories'];
}
