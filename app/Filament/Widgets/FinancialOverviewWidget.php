<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
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
            Stat::make('Total Money I Have', 'ZMW ' . number_format($totalMoney, 2))
                ->description('Active accounts + active savings')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),

            Stat::make('Monthly Income', 'ZMW ' . number_format($totalMonthlyIncome, 2))
                ->description('All active income sources')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Monthly Expenses', 'ZMW ' . number_format($totalMonthlyExpenses, 2))
                ->description('This month\'s spending')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($totalMonthlyExpenses > $totalMonthlyIncome ? 'danger' : 'warning'),

            Stat::make('Cash Flow', 'ZMW ' . number_format($cashFlow, 2))
                ->description($cashFlow >= 0 ? 'Positive cash flow' : 'Overspending this month')
                ->descriptionIcon($cashFlow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($cashFlow >= 0 ? 'success' : 'danger'),

            Stat::make('Total Debt', 'ZMW ' . number_format($totalDebts, 2))
                ->description('Outstanding balances')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),

            Stat::make('Total Savings', 'ZMW ' . number_format($totalSavings, 2))
                ->description('Across all goals')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Net Worth', 'ZMW ' . number_format($netWorth, 2))
                ->description('Assets minus liabilities')
                ->descriptionIcon('heroicon-m-scale')
                ->color($netWorth >= 0 ? 'success' : 'danger'),
        ];
    }
}
