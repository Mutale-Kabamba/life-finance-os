<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'email', 'phone', 'address',
        'tax_number', 'outstanding_balance', 'is_active', 'notes',
    ];

    protected $casts = [
        'outstanding_balance' => 'decimal:2',
        'is_active'           => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
