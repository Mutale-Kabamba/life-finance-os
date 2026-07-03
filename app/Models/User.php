<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'facebook_id',
        'twitter_id',
        'linkedin_id',
        'github_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_calendar_id',
        'google_calendar_sync_token',
        'google_calendar_connected_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_access_token',
        'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'google_access_token' => 'encrypted',
            'google_refresh_token' => 'encrypted',
            'google_token_expires_at' => 'datetime',
            'google_calendar_connected_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function employment(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    public function incomeSources(): HasMany
    {
        return $this->hasMany(IncomeSource::class);
    }

    public function incomeReceipts(): HasMany
    {
        return $this->hasMany(IncomeReceipt::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function accountTransactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function family(): HasOne
    {
        return $this->hasOne(Family::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function financialCalendarEntries(): HasMany
    {
        return $this->hasMany(FinancialCalendar::class);
    }

    public function googleCalendarEventMappings(): HasMany
    {
        return $this->hasMany(GoogleCalendarEventMapping::class);
    }

    public function getTotalMonthlyIncomeAttribute(): float
    {
        return $this->incomeSources()
            ->where('is_active', true)
            ->get()
            ->sum(fn ($source) => $source->monthly_amount);
    }

    public function getTotalAssetsValueAttribute(): float
    {
        return $this->assets()->sum('current_value')
            + $this->investments()->where('status', 'active')->sum('current_value');
    }

    public function getTotalLiabilitiesAttribute(): float
    {
        return $this->debts()->where('status', 'active')->sum('outstanding_balance');
    }

    public function getNetWorthAttribute(): float
    {
        return $this->total_assets_value - $this->total_liabilities;
    }
}
