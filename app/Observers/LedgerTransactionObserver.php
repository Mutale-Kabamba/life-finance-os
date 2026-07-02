<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\LedgerTransaction;
use App\Services\Accounting\JournalPostingService;

class LedgerTransactionObserver
{
    public function __construct(private readonly JournalPostingService $postingService)
    {
    }

    public function saving(LedgerTransaction $transaction): void
    {
        // Child payment rows inherit the parent's owner.
        if ($transaction->parent_transaction_id) {
            $parentUserId = LedgerTransaction::whereKey($transaction->parent_transaction_id)->value('user_id');

            if ($parentUserId) {
                $transaction->user_id = $parentUserId;
            }
        }
    }

    public function saved(LedgerTransaction $transaction): void
    {
        $this->postingService->sync($transaction);
    }
}
