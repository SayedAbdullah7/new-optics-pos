<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangePower extends Model
{
    protected $table = 'range_power';

    protected $fillable = [
        'name',
        'max_sph',
        'min_sph',
        'max_cyl',
        'min_cyl',
        'max_total',
        'min_total',
    ];

    public function lenses()
    {
        return $this->hasMany(Lens::class, 'RangePower_id');
    }

    public function values()
    {
        return $this->hasMany(RangePowerValue::class, 'range_power_id');
    }
}




