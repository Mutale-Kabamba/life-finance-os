<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Debt;
use App\Models\DebtPayment;

class DebtPaymentObserver
{
    public function saved(DebtPayment $payment): void
    {
        $this->sync($payment->debt);
    }

    public function deleted(DebtPayment $payment): void
    {
        $this->sync($payment->debt);
    }

    /**
     * Keep the debt's outstanding balance and status in sync with its payments.
     */
    private function sync(?Debt $debt): void
    {
        if (! $debt) {
            return;
        }

        $paid    = (float) $debt->payments()->sum('amount');
        $balance = max(0, (float) $debt->original_amount - $paid);
        $status  = $debt->status;

        if ($balance <= 0) {
            $status = 'paid_off';
        } elseif ($status === 'paid_off') {
            $status = 'active';
        }

        $debt->forceFill([
            'outstanding_balance' => $balance,
            'status'              => $status,
        ])->saveQuietly();
    }
}
