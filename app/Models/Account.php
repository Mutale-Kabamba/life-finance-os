<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    public const IN_TYPES = ['credit', 'transfer_in', 'adjustment_in'];
    public const OUT_TYPES = ['debit', 'transfer_out', 'adjustment_out'];

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'provider',
        'account_number',
        'currency',
        'opening_balance',
        'current_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function syncBalance(): void
    {
        $in = (float) $this->transactions()->whereIn('type', self::IN_TYPES)->sum('amount');
        $out = (float) $this->transactions()->whereIn('type', self::OUT_TYPES)->sum('amount');
        $balance = (float) $this->opening_balance + $in - $out;

        $this->forceFill([
            'current_balance' => max(0, $balance),
        ])->saveQuietly();
    }
}
