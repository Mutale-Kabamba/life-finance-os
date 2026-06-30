<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    public function __construct(private readonly PostingRuleService $postingRuleService)
    {
    }

    /**
     * Idempotently post a balanced two-line journal entry for a ledger transaction.
     */
    public function sync(LedgerTransaction $transaction): void
    {
        $transaction->loadMissing('account');

        if (! $transaction->account) {
            return;
        }

        $amount = round((float) $transaction->amount, 2);

        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($transaction, $amount): void {
            $businessId = (int) $transaction->business_id;

            $settlement = $this->postingRuleService->settlementAccount($businessId);
            $contra     = $this->postingRuleService->contraAccount($businessId);
            $type       = $this->resolveTransactionType($transaction);

            // Allow a transaction to override the settlement counterpart (e.g. POS
            // sales relieving inventory: Dr COGS / Cr Inventory instead of Cash).
            $counterOverride = $transaction->metadata['counter_account_id'] ?? null;
            $counterId = $counterOverride !== null
                ? (int) $counterOverride
                : (int) $settlement->id;

            [$debitId, $creditId] = $this->resolveAccountPair(
                $type,
                (int) $transaction->account_id,
                $counterId,
                (int) $contra->id,
            );

            $entry = JournalEntry::updateOrCreate(
                ['ledger_transaction_id' => $transaction->id],
                [
                    'business_id' => $businessId,
                    'user_id'     => $transaction->user_id,
                    'entry_date'  => $transaction->date,
                    'reference'   => 'TXN-' . $transaction->id,
                    'description' => $transaction->description,
                    'posted_at'   => now(),
                ]
            );

            $entry->lines()->delete();
            $entry->lines()->createMany([
                [
                    'account_id'  => $debitId,
                    'business_id' => $businessId,
                    'user_id'     => $transaction->user_id,
                    'debit'       => $amount,
                    'credit'      => 0,
                    'memo'        => 'Auto debit leg',
                ],
                [
                    'account_id'  => $creditId,
                    'business_id' => $businessId,
                    'user_id'     => $transaction->user_id,
                    'debit'       => 0,
                    'credit'      => $amount,
                    'memo'        => 'Auto credit leg',
                ],
            ]);
        });
    }

    private function resolveTransactionType(LedgerTransaction $t): string
    {
        $meta = $t->metadata['transaction_type'] ?? null;

        if (is_string($meta) && $meta !== '') {
            return $meta;
        }

        return match ($t->account->type) {
            'income'    => 'money_in',
            'cogs'      => 'money_out_direct',
            'expense'   => 'money_out_general',
            'asset'     => 'valuables',
            'liability' => 'debts',
            default     => 'money_in',
        };
    }

    /**
     * @return array{0:int,1:int} [debitAccountId, creditAccountId]
     */
    private function resolveAccountPair(string $type, int $primary, int $settlement, int $contra): array
    {
        $debit = match ($type) {
            'money_in', 'debts'                                  => $primary === $settlement ? $contra : $settlement,
            'debt_payment'                                       => $primary,
            'money_out_direct', 'money_out_general', 'valuables' => $primary,
            default                                              => $primary,
        };

        $credit = match ($type) {
            'money_in', 'debts'                      => $primary,
            'debt_payment'                           => $primary === $settlement ? $contra : $settlement,
            'money_out_direct', 'money_out_general'  => $primary === $settlement ? $contra : $settlement,
            'valuables'                              => $primary === $settlement ? $contra : $settlement,
            default                                  => $settlement,
        };

        return [$debit, $credit];
    }
}
