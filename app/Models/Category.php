<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model implements TranslatableContract
{
    use Translatable;
    use LogsActivity;

    /**
     * The table associated with the model.
     */
    protected $table = 'categories';

    /**
     * Attributes to translate.
     */
    public $translatedAttributes = ['name'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get products for this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get products count.
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}





