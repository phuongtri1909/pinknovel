<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'comment_id', 'type'];

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function likes()
    {
        return $this->reactions()->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->reactions()->where('type', 'dislike');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
