<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Budget;
use App\Models\BudgetItem;

class BudgetItemObserver
{
    public function saved(BudgetItem $item): void
    {
        $this->sync($item->budget);
    }

    public function deleted(BudgetItem $item): void
    {
        $this->sync($item->budget);
    }

    /**
     * Keep the budget header totals in sync with its line items.
     */
    private function sync(?Budget $budget): void
    {
        if (! $budget) {
            return;
        }

        $budget->forceFill([
            'total_budgeted' => (float) $budget->items()->sum('budgeted_amount'),
            'total_actual'   => (float) $budget->items()->sum('actual_amount'),
        ])->saveQuietly();
    }
}
