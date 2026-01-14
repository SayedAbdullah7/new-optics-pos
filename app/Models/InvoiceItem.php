<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'invoice_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'item_id',
        'user_id',
        'name',
        'quantity',
        'price',
        'total',
        'discount',
        'tax',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    /**
     * Get the invoice for this item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product for this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Get the user who created this item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total.
     */
    public function getTotalCalculatedAttribute(): float
    {
        return ($this->quantity * $this->price) - $this->discount + $this->tax;
    }
}


