<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillLens extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'bill_lenses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'bill_id',
        'lens_id',
        'name',
        'quantity',
        'price',
        'total',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the bill for this lens.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the lens product.
     */
    public function lens()
    {
        return $this->belongsTo(Lens::class);
    }
}
