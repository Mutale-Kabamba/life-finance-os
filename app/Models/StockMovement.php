<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * Sign each movement type applies to quantity_on_hand.
     */
    public const DIRECTIONS = [
        'opening'         => 1,
        'purchase'        => 1,
        'return_in'       => 1,
        'adjustment_in'   => 1,
        'sale'            => -1,
        'return_out'      => -1,
        'adjustment_out'  => -1,
    ];

    public const TYPE_LABELS = [
        'opening'         => 'Opening balance',
        'purchase'        => 'Purchase (stock in)',
        'sale'            => 'Sale (stock out)',
        'return_in'       => 'Customer return (in)',
        'return_out'      => 'Return to supplier (out)',
        'adjustment_in'   => 'Adjustment (increase)',
        'adjustment_out'  => 'Adjustment (decrease)',
    ];

    protected $fillable = [
        'business_id', 'inventory_id', 'user_id', 'type', 'quantity',
        'unit_cost', 'reference', 'ledger_transaction_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'  => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerTransaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class);
    }

    /**
     * Signed effect on stock-on-hand.
     */
    public function signedQuantity(): int
    {
        return (int) $this->quantity * (self::DIRECTIONS[$this->type] ?? 0);
    }
}
