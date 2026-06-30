<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'category', 'target_amount', 'current_amount',
        'target_date', 'monthly_contribution', 'status', 'notes',
    ];

    protected $casts = [
        'target_amount'        => 'decimal:2',
        'current_amount'       => 'decimal:2',
        'monthly_contribution' => 'decimal:2',
        'target_date'          => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount == 0) {
            return 0;
        }

        return min(100, round(($this->current_amount / $this->target_amount) * 100, 2));
    }

    public function getEstimatedCompletionDateAttribute(): ?string
    {
        if ($this->monthly_contribution <= 0 || $this->remaining_amount <= 0) {
            return null;
        }

        $months = ceil($this->remaining_amount / $this->monthly_contribution);

        return now()->addMonths($months)->format('M Y');
    }
}
