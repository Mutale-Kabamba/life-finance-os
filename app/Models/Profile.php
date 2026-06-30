<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth', 'gender',
        'phone', 'national_id', 'province', 'district', 'address', 'avatar',
        'marital_status', 'employment_status', 'housing_type',
        'monthly_housing_cost', 'onboarding_completed', 'active_modules',
        'feature_registry',
    ];

    protected $casts = [
        'date_of_birth'          => 'date',
        'onboarding_completed'   => 'boolean',
        'monthly_housing_cost'   => 'decimal:2',
        'active_modules'         => 'array',
        'feature_registry'       => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Read a boolean flag from the feature_registry JSON column.
     */
    public function hasFeature(string $key): bool
    {
        return (bool) data_get($this->feature_registry, $key, false);
    }
}
