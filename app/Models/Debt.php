<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'creditor_name', 'type', 'original_amount',
        'outstanding_balance', 'monthly_installment', 'interest_rate',
        'total_repayment_amount', 'repayment_frequency',
        'start_date', 'due_date', 'status', 'account_number', 'notes', 'details',
    ];

    protected $casts = [
        'original_amount'     => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'interest_rate'       => 'decimal:2',
        'total_repayment_amount' => 'decimal:2',
        'start_date'          => 'date',
        'due_date'            => 'date',
        'details'             => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    public function getPayoffMonthsAttribute(): ?int
    {
        if ($this->monthly_installment <= 0 || $this->outstanding_balance <= 0) {
            return null;
        }

        return (int) ceil($this->outstanding_balance / $this->monthly_installment);
    }

    public function getDebtFreeProjectionAttribute(): ?string
    {
        $months = $this->payoff_months;

        if ($months === null) {
            return null;
        }

        return now()->addMonths($months)->format('M Y');
    }

    public function getComputedTotalRepaymentAmountAttribute(): float
    {
        if ((float) $this->total_repayment_amount > 0) {
            return (float) $this->total_repayment_amount;
        }

        return (float) $this->original_amount * (1 + ((float) $this->interest_rate / 100));
    }

    public function getEffectiveInterestPercentageAttribute(): float
    {
        $principal = (float) $this->original_amount;
        if ($principal <= 0) {
            return 0;
        }

        return (($this->computed_total_repayment_amount - $principal) / $principal) * 100;
    }

    public function getInterestPayableAttribute(): float
    {
        return max(0, $this->computed_total_repayment_amount - (float) $this->original_amount);
    }
}
