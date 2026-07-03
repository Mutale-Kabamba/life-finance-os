<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use HasFactory;

    /**
     * group_name => type pairing enforced at the application layer.
     */
    public const GROUP_TYPE_MAP = [
        'valuables'     => 'asset',
        'debts'         => 'liability',
        'money_in'      => 'income',
        'direct_costs'  => 'cogs',
        'general_costs' => 'expense',
    ];

    public const SETTLEMENT_CODE = '1100';
    public const CONTRA_CODE = '1199';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id', 'code', 'name', 'type', 'group_name', 'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class, 'account_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }
}
