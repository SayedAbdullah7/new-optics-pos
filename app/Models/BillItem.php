<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'bill_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'bill_id',
        'item_id',
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
     * Get the bill for this item.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the product for this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}





