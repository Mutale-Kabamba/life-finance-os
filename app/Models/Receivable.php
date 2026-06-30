<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'debtor_name', 'phone', 'amount',
        'amount_paid', 'due_date', 'status', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date'    => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getOutstandingAttribute(): float
    {
        return (float) $this->amount - (float) $this->amount_paid;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->status !== 'paid'
            && $this->due_date->isPast();
    }
}
