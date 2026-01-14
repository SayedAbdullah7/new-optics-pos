<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'papers';

    /**
     * The attributes that are mass assignable.
     * Field names match the existing database schema.
     */
    protected $fillable = [
        'client_id',
        'R_sph',
        'R_cyl',
        'R_axis',
        'L_sph',
        'L_cyl',
        'L_axis',
        'addtion',
        'ipd',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'R_sph' => 'decimal:2',
        'R_cyl' => 'decimal:2',
        'R_axis' => 'integer',
        'L_sph' => 'decimal:2',
        'L_cyl' => 'decimal:2',
        'L_axis' => 'integer',
        'addtion' => 'decimal:2',
        'ipd' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client for this paper.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get invoices using this paper.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Format R_sph with + sign for positive values.
     */
    public function getRSphAttribute($value)
    {
        if ($value === null) return '';
        return $value > 0 ? '+' . number_format($value, 2) : number_format($value, 2);
    }

    /**
     * Format R_cyl with + sign for positive values.
     */
    public function getRCylAttribute($value)
    {
        if ($value === null) return '';
        return $value > 0 ? '+' . number_format($value, 2) : number_format($value, 2);
    }

    /**
     * Format L_sph with + sign for positive values.
     */
    public function getLSphAttribute($value)
    {
        if ($value === null) return '';
        return $value > 0 ? '+' . number_format($value, 2) : number_format($value, 2);
    }

    /**
     * Format L_cyl with + sign for positive values.
     */
    public function getLCylAttribute($value)
    {
        if ($value === null) return '';
        return $value > 0 ? '+' . number_format($value, 2) : number_format($value, 2);
    }

    /**
     * Format addition with + sign for positive values.
     */
    public function getAddtionAttribute($value)
    {
        if ($value === null || $value == 0) return '';
        return $value > 0 ? '+' . number_format($value, 2) : number_format($value, 2);
    }

    /**
     * Get raw R_sph value without formatting.
     */
    public function getRawRSph()
    {
        return $this->attributes['R_sph'] ?? null;
    }

    /**
     * Get raw R_cyl value without formatting.
     */
    public function getRawRCyl()
    {
        return $this->attributes['R_cyl'] ?? null;
    }

    /**
     * Get raw L_sph value without formatting.
     */
    public function getRawLSph()
    {
        return $this->attributes['L_sph'] ?? null;
    }

    /**
     * Get raw L_cyl value without formatting.
     */
    public function getRawLCyl()
    {
        return $this->attributes['L_cyl'] ?? null;
    }

    /**
     * Get raw addtion value without formatting.
     */
    public function getRawAddtion()
    {
        return $this->attributes['addtion'] ?? null;
    }
}
