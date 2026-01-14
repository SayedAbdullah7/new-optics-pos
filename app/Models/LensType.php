<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensType extends Model
{
    protected $table = 'lens_types';

    protected $fillable = [
        'name',
    ];

    public function lenses()
    {
        return $this->hasMany(Lens::class, 'type_id');
    }
}




