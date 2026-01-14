<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLens extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'invoice_lenses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'lens_id',
        'user_id',
        'name',
        'price',
        'quantity',
        'total',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    /**
     * Get the invoice for this lens item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the lens product.
     */
    public function lens()
    {
        return $this->belongsTo(Lens::class);
    }

    /**
     * Get the user who added this lens item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total from price and quantity.
     */
    public function calculateTotal(): float
    {
        // For lenses, quantity is typically 2 (pair), so total = price * quantity / 2
        return $this->price * $this->quantity / 2;
    }
}




