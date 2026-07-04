<?php

namespace App\Filament\Widgets;

use App\Services\FinancialIntelligenceService;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DebtOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.debt-overview';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected $listeners = ['personal-period-changed' => 'syncFilters'];

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

    public function syncFilters(string $period, bool $useCustomRange, ?string $rangeStart, ?string $rangeEnd): void
    {
        $this->activePeriod = $period;
        $this->useCustomRange = $useCustomRange;
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;
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

        [$start, $end] = match ($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($this->useCustomRange && $this->rangeStart && $this->rangeEnd) {
            try {
                $start = Carbon::parse($this->rangeStart)->startOfDay();
                $end = Carbon::parse($this->rangeEnd)->endOfDay();
            } catch (\Throwable) {
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
        $periodSuffix = $this->useCustomRange ? 'selected range' : strtolower($period);

        $latestIncome = $user->incomeReceipts()
            ->latest('received_date')
            ->latest('id')
            ->first();

        $allocation = [];
        if ($latestIncome && (float) $latestIncome->amount > 0) {
            $allocation = app(FinancialIntelligenceService::class)
                ->recommendAllocation($user, (float) $latestIncome->amount);
        }

        $debts = collect($analysis['ranked_debts'])
            ->map(function (array $debt) use ($periodFactor): array {
                $debt['monthly_obligation'] = ((float) ($debt['monthly_obligation'] ?? 0)) * $periodFactor;

                return $debt;
            });

        return [
            'debts'      => $debts,
            'totalDebt'  => (float) $debts->sum('outstanding_balance'),
            'totalMonthly' => (float) $debts->sum('monthly_obligation'),
            'availableCash' => (float) $analysis['available_cash'],
            'monthlyExpectedIncome' => ((float) $analysis['monthly_expected_income']) * $periodFactor,
            'mandatoryMonthlyExpenses' => ((float) $analysis['monthly_mandatory_expenses']) * $periodFactor,
            'periodSuffix' => $periodSuffix,
            'latestIncomeAmount' => $latestIncome ? (float) $latestIncome->amount : null,
            'allocation' => $allocation,
        ];
    }
}
