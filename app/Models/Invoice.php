<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'paper_id',
        'invoice_number',
        'order_number',
        'status',
        'invoiced_at',
        'due_at',
        'amount',
        'user_id',
        'contact_tax_number',
        'contact_phone',
        'contact_address',
        'notes',
        'category_id',
        'parent_id',
        'invoice_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'invoiced_at' => 'datetime',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client for this invoice.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the paper/prescription for this invoice.
     */
    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * Get invoice items (products).
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get invoice lenses.
     */
    public function lenses()
    {
        return $this->hasMany(InvoiceLens::class);
    }

    /**
     * Get transactions for this invoice.
     */
    public function transactions($untilDate = null)
    {
        $query = $this->hasMany(Transaction::class, 'document_id')->where('category_id', 1);

        if ($untilDate) {
            return $query->whereDate('paid_at', '<=', $untilDate)->get();
        }

        return $query;
    }

    /**
     * Get the last transaction for this invoice.
     */
    public function lastTransaction()
    {
        return $this->hasMany(Transaction::class, 'document_id')
            ->where('category_id', 1)
            ->latest()
            ->first();
    }

    /**
     * Get the parent invoice (for cancelled invoices).
     */
    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get child invoices (cancellation invoices).
     */
    public function childInvoices()
    {
        return $this->hasMany(Invoice::class, 'invoice_id');
    }

    /**
     * Get paid amount.
     */
    public function getPaidAttribute(): float
    {
        return $this->transactions()->sum('amount');
    }

    /**
     * Get remaining amount.
     */
    public function getRemainingAttribute(): float
    {
        return $this->amount - $this->paid;
    }

    /**
     * Check if invoice is fully paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->remaining <= 0;
    }

    /**
     * Get paid amount until a specific date.
     */
    public function paidUntil($date): float
    {
        return $this->transactions($date)->sum('amount');
    }

    /**
     * Get remaining amount until a specific date.
     */
    public function remainingUntil($date): float
    {
        return $this->amount - $this->paidUntil($date);
    }

    /**
     * Get transaction count.
     */
    public function transactionCount(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Calculate total from items and lenses.
     */
    public function calculateTotal(): float
    {
        $itemsTotal = $this->items()->sum('total');
        $lensesTotal = $this->lenses()->sum('total');
        return $itemsTotal + $lensesTotal;
    }

    /**
     * Update invoice status based on payments.
     */
    public function updateStatus(): void
    {
        $paid = $this->paid;

        if ($paid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
    }

    /**
     * Generate invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::withTrashed()->latest('id')->first();
        $lastId = $lastInvoice ? $lastInvoice->id : 0;
        return 'INV' . ($lastId + 1001);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $date1, $date2)
    {
        return $query->whereBetween('invoiced_at', [
            $date1,
            Carbon::parse($date2)->endOfDay()
        ]);
    }

    /**
     * Scope from date.
     */
    public function scopeFromDate($query, $date)
    {
        return $query->whereDate('invoiced_at', '>=', $date);
    }

    /**
     * Scope to date.
     */
    public function scopeToDate($query, $date)
    {
        return $query->whereDate('invoiced_at', '<=', $date);
    }

    /**
     * Scope before date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('invoiced_at', '<', $date);
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'paid');
    }

    /**
     * Scope for cancelled invoices.
     */
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', ['canceled', 'cancelled']);
    }

    /**
     * Check if invoice is cancelled.
     */
    public function isCancelled(): bool
    {
        return in_array($this->status, ['canceled', 'cancelled']);
    }
}
