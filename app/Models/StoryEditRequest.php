<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'user_id',
        'title',
        'slug',
        'description',
        'cover',
        'cover_medium',
        'cover_thumbnail',
        'author_name',
        'story_type',
        'is_18_plus',
        'categories_data',
        'review_note',
        'status',
        'admin_note',
        'submitted_at',
        'reviewed_at',
        'translator_name'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the story associated with the edit request
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Get the user who submitted the edit request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the categories as an array
     */
    public function getCategoriesAttribute()
    {
        return json_decode($this->categories_data, true) ?? [];
    }
    
    /**
     * Set the categories data as JSON
     */
    public function setCategoriesAttribute($categories)
    {
        $this->attributes['categories_data'] = json_encode($categories);
    }
}
