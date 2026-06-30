<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The primary key is a UUID string, not an auto-incrementing integer.
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'financial_context_id',
        'financial_context_type',
        'chart_of_accounts_code',
        'entry_type',
        'amount',
        'currency_code',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount'    => 'decimal:4',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * The owning user of this ledger entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The polymorphic financial context this entry belongs to
     * (e.g. a PersonalAccount, Company or Business model).
     */
    public function financialContext(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'financial_context_type', 'financial_context_id');
    }
}
