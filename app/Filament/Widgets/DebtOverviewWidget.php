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

        $debtModelsById = $user->debts()
            ->where('status', 'active')
            ->whereIn('id', collect($analysis['ranked_debts'])->pluck('id')->filter()->all())
            ->get()
            ->keyBy('id');

        $debts = collect($analysis['ranked_debts'])
            ->map(function (array $debt) use ($periodFactor, $debtModelsById): array {
                $debt['monthly_obligation'] = ((float) ($debt['monthly_obligation'] ?? 0)) * $periodFactor;

                $details = (array) optional($debtModelsById->get($debt['id'] ?? null))->details;
                $debt['type_label'] = $this->debtTypeLabel((string) ($debt['type'] ?? 'other'));
                $debt['type_context'] = $this->debtTypeContext((string) ($debt['type'] ?? 'other'), $details);

                return $debt;
            });

        $debtTypeMix = $debts
            ->groupBy('type_label')
            ->map(fn ($group, string $label): string => $label . ' (' . $group->count() . ')')
            ->values()
            ->take(4)
            ->implode(' · ');

        return [
            'debts'      => $debts,
            'totalDebt'  => (float) $debts->sum('outstanding_balance'),
            'totalMonthly' => (float) $debts->sum('monthly_obligation'),
            'availableCash' => (float) $analysis['available_cash'],
            'monthlyExpectedIncome' => ((float) $analysis['monthly_expected_income']) * $periodFactor,
            'mandatoryMonthlyExpenses' => ((float) $analysis['monthly_mandatory_expenses']) * $periodFactor,
            'periodSuffix' => $periodSuffix,
            'debtTypeMix' => $debtTypeMix,
            'latestIncomeAmount' => $latestIncome ? (float) $latestIncome->amount : null,
            'allocation' => $allocation,
        ];
    }

    private function debtTypeLabel(string $type): string
    {
        return match ($type) {
            'bank_loan' => 'Bank Loan',
            'mobile_loan' => 'Mobile Loan',
            'mortgage' => 'Mortgage',
            'vehicle_loan' => 'Vehicle Loan',
            'personal_loan' => 'Personal Loan',
            'hire_purchase' => 'Hire Purchase',
            'credit_card' => 'Credit Card',
            'student_loan' => 'Student Loan',
            default => 'Other',
        };
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function debtTypeContext(string $type, array $details): ?string
    {
        return match ($type) {
            'hire_purchase' => $this->hirePurchaseContext($details),
            'mobile_loan' => ! empty($details['mobile_provider'])
                ? 'Provider: ' . str_replace('_', ' ', (string) $details['mobile_provider'])
                : null,
            'mortgage', 'vehicle_loan' => ! empty($details['term_months'])
                ? 'Term: ' . (int) $details['term_months'] . ' months'
                : null,
            default => ! empty($details['reference_number'])
                ? 'Ref: ' . (string) $details['reference_number']
                : null,
        };
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function hirePurchaseContext(array $details): ?string
    {
        $parts = [];

        if (! empty($details['item_name'])) {
            $parts[] = 'Item: ' . (string) $details['item_name'];
        }

        $suggestedInstallment = (float) ($details['suggested_installment'] ?? 0);
        $remainingMonths = max((int) ($details['remaining_term_months'] ?? 0), 0);

        if ($suggestedInstallment > 0 && $remainingMonths > 0) {
            $parts[] = 'Plan: ZMW ' . number_format($suggestedInstallment, 2) . '/month for ~' . $remainingMonths . ' month(s)';
        }

        if (empty($parts)) {
            return null;
        }

        return implode(' | ', $parts);
    }
}
