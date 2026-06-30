<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id', 'user_id', 'amount', 'principal_paid',
        'interest_paid', 'payment_date', 'is_late', 'reference', 'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid'  => 'decimal:2',
        'payment_date'   => 'date',
        'is_late'        => 'boolean',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
