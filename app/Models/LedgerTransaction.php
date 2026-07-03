<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_STATUS_PAID = 'paid';

    /**
     * UI transaction type => required ledger account type.
     */
    public const TYPE_MAP = [
        'money_in'          => 'income',
        'money_out_direct'  => 'cogs',
        'money_out_general' => 'expense',
        'valuables'         => 'asset',
        'debts'             => 'liability',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id', 'user_id', 'amount', 'date', 'account_id',
        'supplier_id', 'parent_transaction_id', 'category_id',
        'description', 'payment_status', 'is_reconciled', 'reconciled_at', 'metadata',
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
            'metadata'      => 'array',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LedgerCategory::class, 'category_id');
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_transaction_id');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_transaction_id');
    }

    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class, 'ledger_transaction_id');
    }

    public function paidAmount(): float
    {
        return round((float) $this->paymentTransactions()->sum('amount'), 2);
    }

    public function remainingAmount(): float
    {
        return round(max((float) $this->amount - $this->paidAmount(), 0), 2);
    }

    public function syncPaymentStatus(): void
    {
        if ($this->parent_transaction_id) {
            return; // children don't carry status
        }

        $paid = $this->paidAmount();
        $total = round((float) $this->amount, 2);

        $status = match (true) {
            $paid <= 0      => self::PAYMENT_STATUS_PENDING,
            $paid >= $total => self::PAYMENT_STATUS_PAID,
            default         => self::PAYMENT_STATUS_PARTIALLY_PAID,
        };

        if ($this->payment_status !== $status) {
            $this->forceFill(['payment_status' => $status])->saveQuietly();
        }
    }
}
