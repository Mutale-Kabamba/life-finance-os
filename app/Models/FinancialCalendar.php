<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCalendar extends Model
{
    use HasFactory;

    protected $table = 'financial_calendar';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'source_type',
        'source_id',
        'source_label',
        'due_date',
        'amount',
        'is_recurring',
        'recurrence_pattern',
        'notify_before',
        'notify_days_before',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'is_recurring' => 'boolean',
            'notify_before' => 'boolean',
            'is_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function googleMappings(): HasMany
    {
        return $this->hasMany(GoogleCalendarEventMapping::class, 'financial_calendar_id');
    }
}
