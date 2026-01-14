<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'locale',
        'name',
    ];

    /**
     * Get the account that owns the translation.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
