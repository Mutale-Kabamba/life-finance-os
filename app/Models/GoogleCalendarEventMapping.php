<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarEventMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'financial_calendar_id',
        'source_type',
        'source_id',
        'google_calendar_id',
        'google_event_id',
        'google_event_etag',
        'last_synced_at',
        'sync_origin',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financialCalendar(): BelongsTo
    {
        return $this->belongsTo(FinancialCalendar::class, 'financial_calendar_id');
    }
}
