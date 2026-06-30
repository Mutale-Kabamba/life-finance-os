<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'income_source_id', 'receivable_payment_id', 'name', 'amount',
        'received_date', 'method', 'reference', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'received_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(IncomeSource::class, 'income_source_id');
    }

    public function receivablePayment(): BelongsTo
    {
        return $this->belongsTo(ReceivablePayment::class);
    }
}
