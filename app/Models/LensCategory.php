<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensCategory extends Model
{
    protected $table = 'lens_categories';

    protected $fillable = [
        'name',
        'brand_name',
        'country_name',
    ];

    public function lenses()
    {
        return $this->hasMany(Lens::class, 'category_id');
    }
}




