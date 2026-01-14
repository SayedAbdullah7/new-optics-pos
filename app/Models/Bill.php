<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Bill extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'bills';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'bill_number',
        'order_number',
        'status',
        'billed_at',
        'due_at',
        'amount',
        'user_id',
        'notes',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'billed_at' => 'datetime',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this bill.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor for this bill.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get bill items.
     */
    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * Get transactions for this bill.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'document_id')->where('category_id', 2);
    }

    /**
     * Get paid amount.
     */
    public function getPaidAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    /**
     * Get balance (remaining amount).
     */
    public function getBalanceAttribute(): float
    {
        return $this->amount - $this->paid;
    }

    /**
     * Generate bill number.
     */
    public static function generateBillNumber(): string
    {
        $lastBill = self::withTrashed()->latest('id')->first();
        $lastId = $lastBill ? $lastBill->id : 0;
        return 'BIL-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $date1, $date2)
    {
        return $query->whereBetween('billed_at', [
            $date1,
            Carbon::parse($date2)->endOfDay()
        ]);
    }

    /**
     * Scope before date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('billed_at', '<', $date);
    }

    /**
     * Get paid amount until a specific date.
     */
    public function paidUntil($date): float
    {
        return $this->transactions()
            ->whereDate('paid_at', '<=', $date)
            ->sum('amount');
    }

    /**
     * Get balance until a specific date.
     */
    public function balanceUntil($date): float
    {
        return $this->amount - $this->paidUntil($date);
    }
}





