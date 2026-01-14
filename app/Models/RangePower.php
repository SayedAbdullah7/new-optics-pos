<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangePower extends Model
{
    protected $table = 'range_power';

    protected $fillable = [
        'name',
    ];

    public function lenses()
    {
        return $this->hasMany(Lens::class, 'RangePower_id');
    }
}




