<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_id',
        'transaction_code',
        'amount',
        'coins',
        'image',
        'status',
        'note',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}