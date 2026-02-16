<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryLedger extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'inventory_ledger';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stockable_type',
        'stockable_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'user_id',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the stockable model (Product or Lens).
     */
    public function stockable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the reference model (Bill, Invoice, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this ledger entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for purchase transactions.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope for sale transactions.
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    /**
     * Scope for return transactions.
     */
    public function scopeReturns($query)
    {
        return $query->whereIn('type', ['purchase_return', 'sale_return']);
    }

    /**
     * Scope for purchase returns.
     */
    public function scopePurchaseReturns($query)
    {
        return $query->where('type', 'purchase_return');
    }

    /**
     * Scope for sale returns.
     */
    public function scopeSaleReturns($query)
    {
        return $query->where('type', 'sale_return');
    }

    /**
     * Scope for adjustments.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('stockable_type', Product::class)
            ->where('stockable_id', $productId);
    }

    /**
     * Scope for a specific lens.
     */
    public function scopeForLens($query, $lensId)
    {
        return $query->where('stockable_type', Lens::class)
            ->where('stockable_id', $lensId);
    }
}
