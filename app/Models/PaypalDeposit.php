<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaypalDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_payment_paypal_id',
        'base_usd_amount',
        'usd_amount',
        'payment_method',
        'vnd_amount',
        'coins',
        'exchange_rate',
        'fee_percent',
        'fee_amount',
        'transaction_code',
        'paypal_email',
        'image',
        'status',
        'note',
        'processed_at',
        'expired_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'usd_amount' => 'decimal:2',
        'vnd_amount' => 'decimal:0',
        'exchange_rate' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'fee_amount' => 'decimal:0',
        'processed_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    // Các status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get user who made the deposit
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted USD amount
     */
    public function getUsdAmountFormattedAttribute()
    {
        return '$' . number_format($this->usd_amount, 2);
    }

    /**
     * Get formatted VND amount
     */
    public function getVndAmountFormattedAttribute()
    {
        return number_format($this->vnd_amount) . ' VNĐ';
    }

    /**
     * Get formatted coins
     */
    public function getCoinsFormattedAttribute()
    {
        return number_format($this->coins);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            default => 'Không xác định'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Check if deposit is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    /**
     * Generate unique transaction code
     */
    public static function generateTransactionCode()
    {
        do {
            $code = 'PP' . strtoupper(substr(md5(time() . rand()), 0, 8));
        } while (self::where('transaction_code', $code)->exists());

        return $code;
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing($note = null)
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'note' => $note,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark as approved and add coins
     */
    public function markAsApproved($note = null, $paypalTransactionId = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'note' => $note,
            'paypal_transaction_id' => $paypalTransactionId,
            'processed_at' => now()
        ]);

        // Add coins to user
        $this->user->increment('coins', $this->coins);

        return true;
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected($note = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'note' => $note,
            'processed_at' => now()
        ]);
    }

    public function requestPaymentPaypal()
    {
        return $this->belongsTo(RequestPaymentPaypal::class, 'request_payment_paypal_id');
    }
}
