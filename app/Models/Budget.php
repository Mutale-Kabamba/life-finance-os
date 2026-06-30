<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'period', 'start_date', 'end_date',
        'total_income', 'total_budgeted', 'total_actual', 'status', 'notes',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'total_income'   => 'decimal:2',
        'total_budgeted' => 'decimal:2',
        'total_actual'   => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function getVarianceAttribute(): float
    {
        return $this->total_budgeted - $this->total_actual;
    }

    public function getUtilizationPercentAttribute(): float
    {
        if ($this->total_budgeted == 0) {
            return 0;
        }

        return round(($this->total_actual / $this->total_budgeted) * 100, 2);
    }
}
