<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'vendors';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'phone' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vendor's name with first letter capitalized.
     */
    public function getNameAttribute($value): string
    {
        return ucfirst($value);
    }

    /**
     * Get bills for this vendor.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get transactions for this vendor.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'contact_id')->where('type', 'expense');
    }

    /**
     * Get total paid amount.
     */
    public function getPaidAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    /**
     * Get total bills amount.
     */
    public function getTotalBillsAttribute(): float
    {
        return $this->bills()->sum('amount');
    }

    /**
     * Get balance (paid - bills).
     */
    public function getBalanceAttribute(): float
    {
        return $this->paid - $this->total_bills;
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
        $billsTotal = $this->bills()->whereDate('billed_at', '<=', $date)->sum('amount');
        return $billsTotal - $this->paidUntil($date);
    }
}





