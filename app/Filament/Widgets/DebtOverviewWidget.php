<?php

namespace App\Filament\Widgets;

use App\Models\Debt;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DebtOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.debt-overview';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $debts = Auth::user()->debts()
            ->where('status', 'active')
            ->orderBy('outstanding_balance')
            ->get();

        return [
            'debts'      => $debts,
            'totalDebt'  => $debts->sum('outstanding_balance'),
            'totalMonthly' => $debts->sum('monthly_installment'),
        ];
    }
}
