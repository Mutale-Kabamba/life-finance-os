<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    public function created(Account $account): void
    {
        $account->syncBalance();
    }

    public function updated(Account $account): void
    {
        if ($account->wasChanged('opening_balance')) {
            $account->syncBalance();
        }
    }
}
