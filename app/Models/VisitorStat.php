<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_visits',
        'unique_visitors',
        'page_views',
        'new_users',
        'returning_users',
        'hourly_stats',
        'page_stats',
    ];

    protected $casts = [
        'date' => 'date',
        'hourly_stats' => 'array',
        'page_stats' => 'array',
    ];
}
