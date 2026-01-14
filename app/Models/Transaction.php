<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Transaction extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'account_id',
        'paid_at',
        'amount',
        'currency_code',
        'currency_rate',
        'document_id',
        'contact_id',
        'description',
        'category_id',
        'payment_method',
        'reference',
        'user_id',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'currency_rate' => 'decimal:4',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account for this transaction.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the invoice for this transaction.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'document_id')->withTrashed();
    }

    /**
     * Get the bill for this transaction.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'document_id')->withTrashed();
    }

    /**
     * Get the vendor for this transaction.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'contact_id');
    }

    /**
     * Get the expense for this transaction.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'document_id');
    }

    /**
     * Scope for sales transactions (category_id = 1).
     */
    public function scopeSales($query)
    {
        return $query->where('category_id', 1);
    }

    /**
     * Scope for purchases transactions (category_id = 2).
     */
    public function scopePurchases($query)
    {
        return $query->where('category_id', 2);
    }

    /**
     * Scope for overheads transactions (category_id = 3).
     */
    public function scopeOverheads($query)
    {
        return $query->where('category_id', 3);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $date1, $date2)
    {
        return $query->whereBetween('paid_at', [
            $date1,
            Carbon::parse($date2)->endOfDay()
        ]);
    }

    /**
     * Scope before date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('paid_at', '<', $date);
    }
}





