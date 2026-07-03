<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\IncomeReceipt;

class IncomeReceiptObserver
{
    public function saved(IncomeReceipt $receipt): void
    {
        $this->syncCashMovement($receipt);
    }

    public function deleted(IncomeReceipt $receipt): void
    {
        if ($receipt->account_transaction_id) {
            AccountTransaction::query()->whereKey($receipt->account_transaction_id)->delete();
        }
    }

    private function syncCashMovement(IncomeReceipt $receipt): void
    {
        if ((float) $receipt->amount <= 0 || ! $receipt->user_id) {
            return;
        }

        $account = $receipt->account;

        if (! $account) {
            $account = Account::query()
                ->where('user_id', $receipt->user_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }

        if (! $account) {
            $account = Account::create([
                'user_id' => $receipt->user_id,
                'name' => 'Cash Wallet',
                'type' => 'cash_wallet',
                'currency' => 'ZMW',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'notes' => 'Auto-created to track income received cash flow.',
            ]);
        }

        $transaction = $receipt->accountTransaction;

        if (! $transaction) {
            $transaction = new AccountTransaction();
        }

        $transaction->fill([
            'account_id' => $account->id,
            'user_id' => $receipt->user_id,
            'type' => 'credit',
            'amount' => $receipt->amount,
            'transaction_date' => $receipt->received_date,
            'reference' => $receipt->reference ?: ('INCOME-' . $receipt->id),
            'description' => $receipt->name,
        ]);
        $transaction->save();

        $receipt->forceFill([
            'account_id' => $account->id,
            'account_transaction_id' => $transaction->id,
        ])->saveQuietly();
    }
}
