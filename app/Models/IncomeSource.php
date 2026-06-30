<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'type', 'amount', 'frequency',
        'start_date', 'is_active', 'notes',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'start_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMonthlyAmountAttribute(): float
    {
        return match ($this->frequency) {
            'daily'      => $this->amount * 30,
            'weekly'     => $this->amount * 4.33,
            'bi_weekly'  => $this->amount * 2.17,
            'monthly'    => $this->amount,
            'quarterly'  => $this->amount / 3,
            'annually'   => $this->amount / 12,
            default      => $this->amount,
        };
    }
}
