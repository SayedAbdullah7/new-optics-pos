<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Account extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'accounts';

    public $translatedAttributes = ['name'];

    protected $fillable = [
        'number',
        'active',
        'default',
    ];

    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
    ];

    /**
     * Get transactions for this account.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get the default account.
     */
    public static function getDefault()
    {
        return self::where('default', true)->first() ?? self::first();
    }
}
