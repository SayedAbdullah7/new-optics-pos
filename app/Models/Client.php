<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /**
     * The table associated with the model.
     * Maps to existing database table.
     */
    protected $table = 'clients';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'phone' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client's name with first letter capitalized.
     */
    public function getNameAttribute($value): string
    {
        return ucfirst($value);
    }

    /**
     * Get invoices for this client.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get papers for this client.
     */
    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    /**
     * Get total invoices amount.
     */
    public function getTotalInvoicesAttribute(): float
    {
        return $this->invoices()->sum('amount');
    }

    /**
     * Get invoices count.
     */
    public function getInvoicesCountAttribute(): int
    {
        return $this->invoices()->count();
    }

    /**
     * Scope for active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for searching clients.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }
}





