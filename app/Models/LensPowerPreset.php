<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensPowerPreset extends Model
{
    protected $table = 'lens_power_presets';

    protected $fillable = [
        'name',
    ];

    public function values()
    {
        return $this->hasMany(LensPowerPresetValue::class, 'preset_id');
    }
}
