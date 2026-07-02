<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\IncomeReceipt;
use App\Models\Receivable;
use App\Models\ReceivablePayment;

class ReceivablePaymentObserver
{
    public function created(ReceivablePayment $payment): void
    {
        $this->sync($payment->receivable);
        $this->mirrorToIncome($payment);
    }

    public function updated(ReceivablePayment $payment): void
    {
        $this->sync($payment->receivable);
        $this->mirrorToIncome($payment);
    }

    public function deleted(ReceivablePayment $payment): void
    {
        // The linked IncomeReceipt is removed automatically via cascade FK.
        $this->sync($payment->receivable);
    }

    /**
     * Keep the receivable's received amount and status in sync with its payments.
     */
    private function sync(?Receivable $receivable): void
    {
        if (! $receivable) {
            return;
        }

        $paid   = (float) $receivable->payments()->sum('amount');
        $status = $receivable->status;

        if ($status !== 'written_off') {
            if ($receivable->amount > 0 && $paid >= (float) $receivable->amount) {
                $status = 'paid';
            } elseif ($paid > 0) {
                $status = 'partially_paid';
            } else {
                $status = 'pending';
            }
        }

        $receivable->forceFill([
            'amount_paid' => $paid,
            'status'      => $status,
        ])->saveQuietly();
    }

    /**
     * Cash collected on a receivable is real money in, so mirror it into the
     * Income Received log (one income entry per collection, kept in sync).
     */
    private function mirrorToIncome(ReceivablePayment $payment): void
    {
        if ((float) $payment->amount <= 0) {
            return;
        }

        $userId = $payment->user_id ?: $payment->receivable?->user_id;

        if (! $userId) {
            return;
        }

        $debtor = $payment->receivable?->debtor_name ?? 'debtor';

        $attributes = [
            'user_id'       => $userId,
            'name'          => 'Collected from ' . $debtor,
            'amount'        => $payment->amount,
            'received_date' => $payment->payment_date,
            'method'        => $payment->method,
            'reference'     => $payment->reference,
            'notes'         => $payment->notes,
        ];

        IncomeReceipt::query()->updateOrCreate(
            ['receivable_payment_id' => $payment->id],
            $attributes,
        );
    }
}
