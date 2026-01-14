<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lens extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'lenses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lens_code',
        'type_id',
        'category_id',
        'RangePower_id',
        'sale_price',
        'purchase_price',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    /**
     * Get the lens category.
     */
    public function category()
    {
        return $this->belongsTo(LensCategory::class);
    }

    /**
     * Get the range power.
     */
    public function rangePower()
    {
        return $this->belongsTo(RangePower::class, 'RangePower_id');
    }

    /**
     * Alias for rangePower relationship (compatibility with old code).
     */
    public function range_power()
    {
        return $this->rangePower();
    }

    /**
     * Get the lens type.
     */
    public function type()
    {
        return $this->belongsTo(LensType::class, 'type_id');
    }

    /**
     * Get lens stock records.
     */
    public function stock()
    {
        return $this->hasMany(LensStock::class);
    }

    /**
     * Get full name of the lens.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [];
        
        if ($this->rangePower) {
            $parts[] = $this->rangePower->name;
        }
        if ($this->type) {
            $parts[] = $this->type->name;
        }
        if ($this->category) {
            $parts[] = $this->category->brand_name;
        }
        
        return implode(' - ', $parts) ?: 'Lens #' . $this->id;
    }
}




