<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'expenses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'amount',
        'date',
        'category',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this expense.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transactions for this expense.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'document_id')->where('type', 'general-Expense');
    }
}





