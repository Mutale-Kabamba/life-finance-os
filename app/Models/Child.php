<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'family_id', 'first_name', 'last_name', 'date_of_birth',
        'school_name', 'grade', 'annual_school_fees', 'monthly_transport',
        'monthly_medical', 'monthly_other', 'notes',
    ];

    protected $casts = [
        'date_of_birth'     => 'date',
        'annual_school_fees' => 'decimal:2',
        'monthly_transport' => 'decimal:2',
        'monthly_medical'   => 'decimal:2',
        'monthly_other'     => 'decimal:2',
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

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function getTotalMonthlyExpenseAttribute(): float
    {
        return ($this->annual_school_fees / 12)
            + $this->monthly_transport
            + $this->monthly_medical
            + $this->monthly_other;
    }
}
