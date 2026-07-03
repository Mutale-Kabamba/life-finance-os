<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'trading_name', 'registration_number',
        'tax_number', 'type', 'industry', 'phone', 'email', 'address',
        'logo', 'currency', 'is_active', 'established_date', 'description',
    ];

    protected $casts = [
        'established_date' => 'date',
        'is_active'        => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function ledgerAccounts(): HasMany
    {
        return $this->hasMany(LedgerAccount::class);
    }

    public function ledgerCategories(): HasMany
    {
        return $this->hasMany(LedgerCategory::class);
    }

    public function ledgerTransactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function cashAtHand(): HasMany
    {
        return $this->hasMany(CashAtHand::class);
    }
}
