<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id', 'expense_category_id', 'name',
        'budgeted_amount', 'actual_amount', 'notes',
        'is_purchased', 'purchased_at', 'account_id', 'expense_id', 'account_transaction_id',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount'   => 'decimal:2',
        'is_purchased'    => 'boolean',
        'purchased_at'    => 'datetime',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function getVarianceAttribute(): float
    {
        return $this->budgeted_amount - $this->actual_amount;
    }

    public function getProgressPercentAttribute(): float
    {
        if ((float) $this->budgeted_amount <= 0) {
            return 0;
        }

        return round(((float) $this->actual_amount / (float) $this->budgeted_amount) * 100, 1);
    }
}
