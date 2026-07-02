<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FinancialOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-overview';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();

        $totalAccountsBalance = 0.0;
        if (Schema::hasTable('accounts')) {
            $totalAccountsBalance = (float) $user->accounts()
                ->where('is_active', true)
                ->sum('current_balance');
        }

        $totalMonthlyIncome = $user->incomeSources()
            ->where('is_active', true)->get()
            ->sum(fn ($s) => $s->monthly_amount);

        $totalMonthlyExpenses = $user->expenses()
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');

        $totalDebts = $user->debts()
            ->where('status', 'active')
            ->sum('outstanding_balance');

        $totalSavings = $user->savingsGoals()
            ->where('status', 'active')
            ->sum('current_amount');

        $totalMoney = $totalAccountsBalance + (float) $totalSavings;

        $netWorth = $user->net_worth;

        $cashFlow = $totalMonthlyIncome - $totalMonthlyExpenses;

        return [
            'cards' => [
                [
                    'title' => 'Total Money I Have',
                    'value' => 'ZMW ' . number_format($totalMoney, 2),
                    'note'  => 'Active accounts + active savings',
                    'icon'  => 'heroicon-m-wallet',
                    'theme' => 'mint',
                ],
                [
                    'title' => 'Monthly Income',
                    'value' => 'ZMW ' . number_format($totalMonthlyIncome, 2),
                    'note'  => 'All active income sources',
                    'icon'  => 'heroicon-m-arrow-trending-up',
                    'theme' => 'blue',
                ],
                [
                    'title' => 'Monthly Expenses',
                    'value' => 'ZMW ' . number_format($totalMonthlyExpenses, 2),
                    'note'  => "This month's spending",
                    'icon'  => 'heroicon-m-arrow-trending-down',
                    'theme' => 'amber',
                ],
                [
                    'title' => 'Cash Flow',
                    'value' => 'ZMW ' . number_format($cashFlow, 2),
                    'note'  => $cashFlow >= 0 ? 'Positive cash flow' : 'Overspending this month',
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
}
