<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Appstract\Stock\HasStock;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements TranslatableContract
{
    use Translatable;
    use HasStock;

    /**
     * The table associated with the model.
     */
    protected $table = 'products';

    /**
     * Attributes to translate.
     */
    public $translatedAttributes = ['name', 'description'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'item_code',
        'category_id',
        'purchase_price',
        'sale_price',
        'stock',
        'image',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['stock'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category for this product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get profit margin.
     */
    public function getProfitAttribute(): float
    {
        return $this->sale_price - $this->purchase_price;
    }

    /**
     * Get profit percentage.
     */
    public function getProfitPercentAttribute(): float
    {
        if ($this->purchase_price == 0) {
            return 0;
        }
        return round(($this->profit / $this->purchase_price) * 100, 2);
    }

    /**
     * Get image path.
     */
    public function getImagePathAttribute(): string
    {
        if ($this->image) {
            return asset('uploads/product_images/' . $this->image);
        }
        return asset('default.png');
    }


    /**
     * Scope for low stock products.
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold);
    }

    /**
     * Scope for in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Get total quantity sold (from invoices).
     * Note: Used for statistics only, not for stock calculation.
     */
    public function sold()
    {
        return $this->hasMany(InvoiceItem::class, 'item_id')->sum('quantity');
    }

    /**
     * Get total quantity bought (from bills).
     * Note: Used for statistics only, not for stock calculation.
     */
    public function bought()
    {
        return $this->hasMany(BillItem::class, 'item_id')->sum('quantity');
    }

    /**
     * Get stock attribute: base stock from DB + stock from HasStock mutations.
     * Override HasStock's getStockAttribute to combine base stock with mutations.
     *
     * @param mixed $value The raw stock value from database
     * @return int Combined stock (base + mutations)
     */
    public function getStockAttribute($value)
    {
        // Get base stock from database column ($value is the raw DB value)
        $baseStock = (int) ($value ?? 0);

        // Call HasStock's stock() method to get mutations stock
        // stock() method from HasStock trait calculates from stockMutations
        // We need to bypass the accessor to avoid infinite loop, so we call the relationship directly
        $mutationsStock = (int) $this->stockMutations()->sum('amount');

        // Return combined stock: base stock from DB + stock mutations
        return $baseStock + $mutationsStock;
    }

    /**
     * Image path method (for backward compatibility).
     */
    public function image_path()
    {
        return $this->image_path;
    }
}
