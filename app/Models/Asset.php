<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'type', 'purchase_price', 'purchase_date',
        'current_value', 'depreciation_rate', 'location', 'serial_number',
        'is_insured', 'insurance_provider', 'insurance_expiry', 'notes',
    ];

    protected $casts = [
        'purchase_price'   => 'decimal:2',
        'current_value'    => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'purchase_date'    => 'date',
        'insurance_expiry' => 'date',
        'is_insured'       => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceHistory(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }
}
