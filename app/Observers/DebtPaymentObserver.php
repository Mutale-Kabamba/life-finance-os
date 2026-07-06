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

        $paid = (float) $debt->payments()->sum('amount');
        $payableBase = $this->payableBaseAmount($debt);
        $balance = max(0, $payableBase - $paid);
        $status  = $debt->status;
        $details = (array) $debt->details;

        if ($debt->type === 'hire_purchase') {
            $termMonths = max((int) ($details['term_months'] ?? 0), 0);
            $suggestedInstallment = (float) ($details['suggested_installment'] ?? 0);

            if ($termMonths > 0 && $payableBase > 0 && $suggestedInstallment <= 0) {
                $suggestedInstallment = round($payableBase / $termMonths, 2);
            }

            if ($suggestedInstallment > 0) {
                $details['remaining_term_months'] = max((int) ceil($balance / $suggestedInstallment), 0);
                $details['suggested_installment'] = $suggestedInstallment;
            }

            $details['financed_amount'] = round($payableBase, 2);
        }

        if ($balance <= 0) {
            $status = 'paid_off';
        } elseif ($status === 'paid_off') {
            $status = 'active';
        }

        $debt->forceFill([
            'outstanding_balance' => $balance,
            'status'              => $status,
            'details'             => $details,
        ])->saveQuietly();
    }

    private function payableBaseAmount(Debt $debt): float
    {
        if ($debt->type !== 'hire_purchase') {
            $totalRepayment = (float) ($debt->total_repayment_amount ?? 0);
            if ($totalRepayment > 0) {
                return $totalRepayment;
            }

            return max(0, (float) $debt->original_amount);
        }

        $details = (array) $debt->details;
        $deposit = (float) ($details['deposit_amount'] ?? 0);
        $totalRepayment = (float) ($debt->total_repayment_amount ?? 0);

        if ($totalRepayment > 0) {
            return max($totalRepayment - $deposit, 0);
        }

        return max((float) $debt->original_amount - $deposit, 0);
    }
}
