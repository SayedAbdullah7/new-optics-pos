<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangePowerValue extends Model
{
    protected $table = 'range_power_values';

    public $timestamps = false;

    protected $fillable = [
        'range_power_id',
        'sph',
        'cyl',
    ];

    protected $casts = [
        'sph' => 'float',
        'cyl' => 'float',
    ];

    public function rangePower()
    {
        return $this->belongsTo(RangePower::class, 'range_power_id');
    }
}
