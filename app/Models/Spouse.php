<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'family_id', 'first_name', 'last_name',
        'date_of_birth', 'phone', 'national_id',
        'employment_status', 'monthly_income', 'marriage_date',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'marriage_date'  => 'date',
        'monthly_income' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
