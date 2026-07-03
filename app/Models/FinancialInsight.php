<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialInsight extends Model
{
    use HasFactory;

    protected $table = 'financial_insights';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'icon',
        'is_read',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
