<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensPowerPresetValue extends Model
{
    protected $table = 'lens_power_preset_values';

    public $timestamps = false;

    protected $fillable = [
        'preset_id',
        'sph',
        'cyl',
    ];

    protected $casts = [
        'sph' => 'float',
        'cyl' => 'float',
    ];

    public function preset()
    {
        return $this->belongsTo(LensPowerPreset::class, 'preset_id');
    }
}
