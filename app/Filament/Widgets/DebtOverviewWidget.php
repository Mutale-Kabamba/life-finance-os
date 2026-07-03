<?php

namespace App\Filament\Widgets;

use App\Services\FinancialIntelligenceService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DebtOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.debt-overview';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();
        $analysis = app(FinancialIntelligenceService::class)->analyze($user);

        $latestIncome = $user->incomeReceipts()
            ->latest('received_date')
            ->latest('id')
            ->first();

        $allocation = [];
        if ($latestIncome && (float) $latestIncome->amount > 0) {
            $allocation = app(FinancialIntelligenceService::class)
                ->recommendAllocation($user, (float) $latestIncome->amount);
        }

        $debts = collect($analysis['ranked_debts']);

        return [
            'debts'      => $debts,
            'totalDebt'  => (float) $debts->sum('outstanding_balance'),
            'totalMonthly' => (float) $debts->sum('monthly_obligation'),
            'availableCash' => (float) $analysis['available_cash'],
            'monthlyExpectedIncome' => (float) $analysis['monthly_expected_income'],
            'mandatoryMonthlyExpenses' => (float) $analysis['monthly_mandatory_expenses'],
            'latestIncomeAmount' => $latestIncome ? (float) $latestIncome->amount : null,
            'allocation' => $allocation,
        ];
    }
}
