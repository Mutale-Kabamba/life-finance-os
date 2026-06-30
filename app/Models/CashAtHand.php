<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAtHand extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cash_at_hand';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id', 'date', 'type', 'amount', 'description', 'reference',
        'ledger_transaction_id', 'user_id', 'is_reconciled',
        'reconciled_at', 'reconciliation_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'amount'        => 'decimal:2',
            'is_reconciled' => 'boolean',
            'reconciled_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReference(int $businessId): string
    {
        $year = date('Y');
        $count = self::withTrashed()
            ->where('business_id', $businessId)
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('CAH-%s-%04d', $year, $count + 1);
    }

    public static function getBalanceAsAt(int $businessId, string $date): float
    {
        $balance = 0.0;

        self::where('business_id', $businessId)
            ->where('date', '<=', $date)
            ->orderBy('date')
            ->orderBy('id')
            ->chunk(100, function ($records) use (&$balance): void {
                foreach ($records as $r) {
                    $balance += in_array($r->type, ['withdrawal', 'bank_deposit'], true)
                        ? -(float) $r->amount
                        : (float) $r->amount;
                }
            });

        return round($balance, 2);
    }

    /**
     * @return array<string, float>
     */
    public static function getSummary(int $businessId, string $startDate, string $endDate): array
    {
        $opening = self::getBalanceAsAt(
            $businessId,
            \Illuminate\Support\Carbon::parse($startDate)->subDay()->toDateString()
        );

        $scope = fn (Builder $q) => $q->where('business_id', $businessId)
            ->whereBetween('date', [$startDate, $endDate]);

        $deposits = (float) self::query()->where($scope)->where('type', 'deposit')->sum('amount');
        $withdrawals = (float) self::query()->where($scope)->where('type', 'withdrawal')->sum('amount');
        $bankDeposits = (float) self::query()->where($scope)->where('type', 'bank_deposit')->sum('amount');

        return [
            'opening_balance'   => round($opening, 2),
            'total_deposits'    => round($deposits, 2),
            'total_withdrawals' => round($withdrawals, 2),
            'total_bank_deposits' => round($bankDeposits, 2),
            'closing_balance'   => round($opening + $deposits - $withdrawals - $bankDeposits, 2),
        ];
    }
}
