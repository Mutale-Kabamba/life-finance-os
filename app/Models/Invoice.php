<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'customer_id', 'invoice_number', 'type',
        'issue_date', 'due_date', 'subtotal', 'tax_amount', 'discount_amount',
        'total_amount', 'amount_paid', 'status', 'notes',
    ];

    protected $casts = [
        'issue_date'       => 'date',
        'due_date'         => 'date',
        'subtotal'         => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'amount_paid'      => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date?->isPast() && $this->status !== 'paid';
    }
}
