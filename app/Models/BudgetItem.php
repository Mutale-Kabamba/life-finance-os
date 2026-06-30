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
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount'   => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function getVarianceAttribute(): float
    {
        return $this->budgeted_amount - $this->actual_amount;
    }
}
