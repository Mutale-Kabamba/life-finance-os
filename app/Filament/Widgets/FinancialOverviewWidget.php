<?php

namespace App\Filament\Widgets;

use App\Services\FinancialIntelligenceService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class FinancialOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-overview';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public string $activePeriod = 'monthly';
    public ?string $rangeStart = null;
    public ?string $rangeEnd = null;
    public bool $useCustomRange = false;

    public function mount(): void
    {
        $this->activePeriod = (string) session('dashboard_filters.personal.period', 'monthly');
        $this->rangeStart = (string) session('dashboard_filters.personal.range_start', now()->startOfMonth()->toDateString());
        $this->rangeEnd = (string) session('dashboard_filters.personal.range_end', now()->endOfMonth()->toDateString());
        $this->useCustomRange = (bool) session('dashboard_filters.personal.use_custom_range', false);
    }

    public function setPeriod(string $period): void
    {
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (! in_array($period, $allowedPeriods, true)) {
            return;
        }

        $this->activePeriod = $period;
        $this->useCustomRange = false;

        session([
            'dashboard_filters.personal.period' => $this->activePeriod,
            'dashboard_filters.personal.range_start' => $this->rangeStart,
            'dashboard_filters.personal.range_end' => $this->rangeEnd,
            'dashboard_filters.personal.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('personal-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function applyDateRange(): void
    {
        if (! $this->rangeStart || ! $this->rangeEnd) {
            return;
        }

        try {
            $start = Carbon::parse($this->rangeStart)->startOfDay();
            $end = Carbon::parse($this->rangeEnd)->endOfDay();
        } catch (\Throwable) {
            return;
        }

        if ($start->gt($end)) {
            [$this->rangeStart, $this->rangeEnd] = [$end->toDateString(), $start->toDateString()];
        }

        $this->useCustomRange = true;

        session([
            'dashboard_filters.personal.period' => $this->activePeriod,
            'dashboard_filters.personal.range_start' => $this->rangeStart,
            'dashboard_filters.personal.range_end' => $this->rangeEnd,
            'dashboard_filters.personal.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('personal-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function clearDateRange(): void
    {
        $this->useCustomRange = false;

        session([
            'dashboard_filters.personal.period' => $this->activePeriod,
            'dashboard_filters.personal.range_start' => $this->rangeStart,
            'dashboard_filters.personal.range_end' => $this->rangeEnd,
            'dashboard_filters.personal.use_custom_range' => $this->useCustomRange,
        ]);

        $this->dispatch('personal-period-changed',
            period: $this->activePeriod,
            useCustomRange: $this->useCustomRange,
            rangeStart: $this->rangeStart,
            rangeEnd: $this->rangeEnd,
        );
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        $analysis = app(FinancialIntelligenceService::class)->analyze($user);

        $period = $this->activePeriod;
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'monthly';
            $this->activePeriod = 'monthly';
        }

        $periodLabel = match ($period) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'yearly' => 'Yearly',
            default => 'Monthly',
        };

        [$periodStart, $periodEnd] = match ($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $start = $periodStart;
        $end = $periodEnd;

        if ($this->useCustomRange && $this->rangeStart && $this->rangeEnd) {
            try {
                $start = Carbon::parse($this->rangeStart)->startOfDay();
                $end = Carbon::parse($this->rangeEnd)->endOfDay();
            } catch (\Throwable) {
                $start = $periodStart;
                $end = $periodEnd;
                $this->useCustomRange = false;
            }

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
                $this->rangeStart = $start->toDateString();
                $this->rangeEnd = $end->toDateString();
            }
        }

        $daysInRange = max($start->diffInDays($end) + 1, 1);
        $periodFactor = $daysInRange / 30;

        $description = $this->useCustomRange
            ? 'Showing transactions from ' . $start->format('M j, Y') . ' to ' . $end->format('M j, Y') . '.'
            : $this->getPeriodDescription($period);

        $totalAccountsBalance = 0.0;
        if (Schema::hasTable('accounts')) {
            $totalAccountsBalance = (float) $user->accounts()
                ->where('is_active', true)
                ->sum('current_balance');
        }

        $totalMonthlyIncome = (float) $analysis['monthly_expected_income'];

        $totalMonthlyExpenses = (float) $analysis['monthly_recurring_expenses'];

        $periodIncome = $totalMonthlyIncome * $periodFactor;
        $periodExpenses = $totalMonthlyExpenses * $periodFactor;

        $totalDebts = $user->debts()
            ->where('status', 'active')
            ->sum('outstanding_balance');

        $totalSavings = $user->savingsGoals()
            ->where('status', 'active')
            ->sum('current_amount');

        $totalMoney = (float) $analysis['available_cash'];

        $netWorth = $user->net_worth;

        $cashFlow = $periodIncome - $periodExpenses;

        return [
            'description' => $description,
            'activePeriod' => $period,
            'periodFilters' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
            ],
            'rangeStart' => $this->rangeStart,
            'rangeEnd' => $this->rangeEnd,
            'useCustomRange' => $this->useCustomRange,
            'cards' => [
                [
                    'title' => 'Total Money I Have',
                    'value' => 'ZMW ' . number_format($totalMoney, 2),
                    'note'  => 'Active accounts + active savings',
                    'icon'  => 'heroicon-m-wallet',
                    'theme' => 'mint',
                ],
                [
                    'title' => ($this->useCustomRange ? 'Selected Range' : $periodLabel) . ' Income',
                    'value' => 'ZMW ' . number_format($periodIncome, 2),
                    'note'  => 'Scaled from recurring monthly income',
                    'icon'  => 'heroicon-m-arrow-trending-up',
                    'theme' => 'blue',
                ],
                [
                    'title' => ($this->useCustomRange ? 'Selected Range' : $periodLabel) . ' Expenses',
                    'value' => 'ZMW ' . number_format($periodExpenses, 2),
                    'note'  => 'Scaled from recurring monthly obligations',
                    'icon'  => 'heroicon-m-arrow-trending-down',
                    'theme' => 'amber',
                ],
                [
                    'title' => ($this->useCustomRange ? 'Selected Range' : $periodLabel) . ' Cash Flow',
                    'value' => 'ZMW ' . number_format($cashFlow, 2),
                    'note'  => $cashFlow >= 0 ? 'Positive cash flow in selected period' : 'Overspending in selected period',
                    'icon'  => $cashFlow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle',
                    'theme' => $cashFlow >= 0 ? 'cyan' : 'rose',
                ],
                [
                    'title' => 'Total Debt',
                    'value' => 'ZMW ' . number_format($totalDebts, 2),
                    'note'  => 'Outstanding balances',
                    'icon'  => 'heroicon-m-credit-card',
                    'theme' => 'rose',
                ],
                [
                    'title' => 'Total Savings',
                    'value' => 'ZMW ' . number_format($totalSavings, 2),
                    'note'  => 'Across all goals',
                    'icon'  => 'heroicon-m-banknotes',
                    'theme' => 'indigo',
                ],
                [
                    'title' => 'Net Worth',
                    'value' => 'ZMW ' . number_format($netWorth, 2),
                    'note'  => 'Assets minus liabilities',
                    'icon'  => 'heroicon-m-scale',
                    'theme' => $netWorth >= 0 ? 'mint' : 'rose',
                ],
            ],
        ];
    }

    protected function getPeriodDescription(string $period): string
    {
        $today = now();

        return match ($period) {
            'daily' => 'Showing Today\'s Transactions | ' . $today->format('D, M j, Y'),
            'weekly' => 'Showing This Week\'s Transactions | Week ' . $this->getWeekOfMonth($today) . ', ' . $today->format('M Y'),
            'yearly' => 'Showing This Year\'s Transactions | ' . $today->format('Y'),
            default => 'Showing This Month\'s Transactions | ' . $today->format('M, Y'),
        };
    }

    protected function getWeekOfMonth(Carbon $date): int
    {
        return (int) ceil(($date->day + $date->copy()->startOfMonth()->dayOfWeekIso - 1) / 7);
    }
}
