<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SavingsProgressWidget extends Widget
{
    protected static string $view = 'filament.widgets.savings-progress';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $goals = Auth::user()->savingsGoals()
            ->where('status', 'active')
            ->get()
            ->map(function ($goal) {
                return [
                    'name'          => $goal->name,
                    'category'      => $goal->category,
                    'target'        => $goal->target_amount,
                    'current'       => $goal->current_amount,
                    'remaining'     => $goal->remaining_amount,
                    'progress'      => $goal->progress_percent,
                    'estimated_date' => $goal->estimated_completion_date,
                ];
            });

        return ['goals' => $goals];
    }
}
