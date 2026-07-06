<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'type', 'institution', 'initial_amount',
        'current_value', 'expected_return_rate', 'start_date',
        'maturity_date', 'status', 'notes', 'details',
    ];

    protected $casts = [
        'initial_amount'       => 'decimal:2',
        'current_value'        => 'decimal:2',
        'expected_return_rate' => 'decimal:2',
        'start_date'           => 'date',
        'maturity_date'        => 'date',
        'details'              => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReturnAmountAttribute(): float
    {
        return $this->current_value - $this->initial_amount;
    }

    public function getReturnPercentAttribute(): float
    {
        if ($this->initial_amount == 0) {
            return 0;
        }

        return round(($this->return_amount / $this->initial_amount) * 100, 2);
    }
}
