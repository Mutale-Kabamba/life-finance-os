<?php

namespace App\Observers;

use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;

class SavingsTransactionObserver
{
    public function created(SavingsTransaction $transaction): void
    {
        $this->sync($transaction->savingsGoal);
    }

    public function updated(SavingsTransaction $transaction): void
    {
        if ($transaction->wasChanged('savings_goal_id')) {
            $originalGoalId = (int) $transaction->getOriginal('savings_goal_id');
            if ($originalGoalId > 0) {
                $this->sync(SavingsGoal::find($originalGoalId));
            }
        }

        $this->sync($transaction->savingsGoal);
    }

    public function deleted(SavingsTransaction $transaction): void
    {
        $this->sync($transaction->savingsGoal);
    }

    private function sync(?SavingsGoal $goal): void
    {
        if (! $goal) {
            return;
        }

        $deposits = (float) $goal->transactions()->where('type', 'deposit')->sum('amount');
        $withdrawals = (float) $goal->transactions()->where('type', 'withdrawal')->sum('amount');
        $current = max(0, $deposits - $withdrawals);

        $status = $goal->status;
        if (! in_array($status, ['cancelled', 'paused'], true)) {
            $status = ((float) $goal->target_amount > 0 && $current >= (float) $goal->target_amount)
                ? 'completed'
                : 'active';
        }

        $goal->forceFill([
            'current_amount' => $current,
            'status' => $status,
        ])->saveQuietly();
    }
}
