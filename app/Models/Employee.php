<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'first_name', 'last_name', 'email', 'phone',
        'position', 'department', 'gross_salary', 'hire_date',
        'termination_date', 'status', 'national_id', 'tpin',
        'bank_name', 'bank_account',
    ];

    protected $casts = [
        'gross_salary'      => 'decimal:2',
        'hire_date'         => 'date',
        'termination_date'  => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
