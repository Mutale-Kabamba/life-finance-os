<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\AccountTransaction;

class AccountTransactionObserver
{
    public function created(AccountTransaction $transaction): void
    {
        $this->sync($transaction->account);
    }

    public function updated(AccountTransaction $transaction): void
    {
        if ($transaction->wasChanged('account_id')) {
            $originalAccountId = (int) $transaction->getOriginal('account_id');
            if ($originalAccountId > 0) {
                $this->sync(Account::find($originalAccountId));
            }
        }

        $this->sync($transaction->account);
    }

    public function deleted(AccountTransaction $transaction): void
    {
        $this->sync($transaction->account);
    }

    private function sync(?Account $account): void
    {
        if (! $account) {
            return;
        }

        $account->syncBalance();
    }
}
