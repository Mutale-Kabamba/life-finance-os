<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAtHandReconciliation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id', 'reconciliation_date', 'opening_balance',
        'total_deposits', 'total_withdrawals', 'expected_balance',
        'actual_balance', 'variance', 'status', 'notes', 'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'opening_balance'     => 'decimal:2',
            'total_deposits'      => 'decimal:2',
            'total_withdrawals'   => 'decimal:2',
            'expected_balance'    => 'decimal:2',
            'actual_balance'      => 'decimal:2',
            'variance'            => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasVariance(): bool
    {
        return $this->status === 'variance';
    }
}
