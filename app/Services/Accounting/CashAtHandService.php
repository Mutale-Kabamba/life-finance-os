<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\CashAtHand;
use App\Models\CashAtHandReconciliation;
use App\Models\LedgerAccount;
use App\Models\LedgerTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashAtHandService
{
    /**
     * @param  array<string, mixed>  $data  requires business_id, type, amount
     */
    public function recordCashMovement(array $data): CashAtHand
    {
        return DB::transaction(function () use ($data): CashAtHand {
            $businessId = (int) $data['business_id'];

            $cah = new CashAtHand([
                'business_id'   => $businessId,
                'date'          => $data['date'] ?? now()->toDateString(),
                'type'          => $data['type'],
                'amount'        => $data['amount'],
                'description'   => $data['description'] ?? null,
                'reference'     => CashAtHand::generateReference($businessId),
                'user_id'       => $data['user_id'] ?? auth()->id(),
                'is_reconciled' => false,
            ]);

            if ($data['create_transaction'] ?? false) {
                $cah->ledger_transaction_id = $this->createLinkedTransaction($data, $businessId)->id;
            }

            $cah->save();

            return $cah;
        });
    }

    /**
     * Move drawer cash to a bank/asset account (creates a bank_deposit record + ledger transaction).
     *
     * @param  array<string, mixed>  $data
     */
    public function depositToBank(array $data): CashAtHand
    {
        $data['type'] = 'bank_deposit';
        $data['create_transaction'] = true;

        return $this->recordCashMovement($data);
    }

    public function reconcileDaily(int $businessId, Carbon $date, float $actualBalance, ?string $notes = null): CashAtHandReconciliation
    {
        $summary  = CashAtHand::getSummary($businessId, $date->toDateString(), $date->toDateString());
        $expected = $summary['closing_balance'];
        $variance = round($expected - $actualBalance, 2);

        $reconciliation = CashAtHandReconciliation::updateOrCreate(
            ['business_id' => $businessId, 'reconciliation_date' => $date->toDateString()],
            [
                'opening_balance'   => $summary['opening_balance'],
                'total_deposits'    => $summary['total_deposits'],
                'total_withdrawals' => $summary['total_withdrawals'] + $summary['total_bank_deposits'],
                'expected_balance'  => $expected,
                'actual_balance'    => $actualBalance,
                'variance'          => $variance,
                'status'            => abs($variance) === 0.0 ? 'reconciled' : 'variance',
                'notes'             => $notes,
                'user_id'           => auth()->id(),
            ]
        );

        if (abs($variance) === 0.0) {
            CashAtHand::query()
                ->where('business_id', $businessId)
                ->whereDate('date', $date->toDateString())
                ->update(['is_reconciled' => true, 'reconciled_at' => now()]);
        }

        return $reconciliation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createLinkedTransaction(array $data, int $businessId): LedgerTransaction
    {
        [$accountCode, $txnType] = match ($data['type']) {
            'deposit'      => ['4100', 'money_in'],
            'withdrawal'   => ['6100', 'money_out_general'],
            'bank_deposit' => [LedgerAccount::SETTLEMENT_CODE, 'valuables'],
            default        => ['4100', 'money_in'],
        };

        $accountId = $data['counter_account_id']
            ?? LedgerAccount::query()
                ->where('business_id', $businessId)
                ->where('code', $accountCode)
                ->value('id');

        return LedgerTransaction::create([
            'business_id'    => $businessId,
            'user_id'        => $data['user_id'] ?? auth()->id(),
            'amount'         => $data['amount'],
            'date'           => $data['date'] ?? now()->toDateString(),
            'account_id'     => $accountId,
            'description'    => $data['description'] ?? 'Cash movement',
            'payment_status' => 'paid',
            'metadata'       => ['transaction_type' => $txnType, 'source' => 'cash_at_hand'],
        ]);
    }
}
