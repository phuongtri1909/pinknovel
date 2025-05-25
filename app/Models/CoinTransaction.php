<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'type', // 'add' or 'subtract'
        'note',
    ];

    /**
     * Get the user that received/lost the coins
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who executed the transaction
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
} 