<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\LedgerAccount;
use App\Models\LedgerTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LedgerSummaryService
{
    /**
     * Base query over journal lines joined to their account, scoped to a business
     * and (optionally) a date range on the parent journal entry.
     */
    private function lines(int $businessId, ?string $start = null, ?string $end = null)
    {
        $query = DB::table('journal_lines as jl')
            ->join('ledger_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('jl.business_id', $businessId);

        if ($start !== null) {
            $query->whereDate('je.entry_date', '>=', $start);
        }

        if ($end !== null) {
            $query->whereDate('je.entry_date', '<=', $end);
        }

        return $query;
    }

    /**
     * @return array<string, float>
     */
    public function incomeStatement(int $businessId, string $start, string $end): array
    {
        $income = (float) $this->lines($businessId, $start, $end)
            ->where('a.type', 'income')
            ->sum(DB::raw('jl.credit - jl.debit'));

        $directCosts = (float) $this->lines($businessId, $start, $end)
            ->where('a.type', 'cogs')
            ->sum(DB::raw('jl.debit - jl.credit'));

        $generalExpenses = (float) $this->lines($businessId, $start, $end)
            ->where('a.type', 'expense')
            ->sum(DB::raw('jl.debit - jl.credit'));

        $grossProfit = $income - $directCosts;

        return [
            'total_income'     => round($income, 2),
            'direct_costs'     => round($directCosts, 2),
            'gross_profit'     => round($grossProfit, 2),
            'general_expenses' => round($generalExpenses, 2),
            'net_profit'       => round($grossProfit - $generalExpenses, 2),
        ];
    }

    /**
     * @return array<string, float>
     */
    public function balanceSheet(int $businessId, string $asOf): array
    {
        $assets = (float) $this->lines($businessId, null, $asOf)
            ->where('a.type', 'asset')
            ->sum(DB::raw('jl.debit - jl.credit'));

        $liabilities = (float) $this->lines($businessId, null, $asOf)
            ->where('a.type', 'liability')
            ->sum(DB::raw('jl.credit - jl.debit'));

        $equity = $assets - $liabilities;

        return [
            'total_assets'      => round($assets, 2),
            'total_liabilities' => round($liabilities, 2),
            'equity'            => round($equity, 2),
            'equation_gap'      => round($assets - ($liabilities + $equity), 2),
        ];
    }

    /**
     * @return array{accounts: array<int, array<string, mixed>>, total_debit: float, total_credit: float, difference: float}
     */
    public function trialBalance(int $businessId, string $start, string $end): array
    {
        $rows = LedgerAccount::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function (LedgerAccount $account) use ($businessId, $start, $end): array {
                $debit = (float) $this->lines($businessId, $start, $end)
                    ->where('jl.account_id', $account->id)
                    ->sum('jl.debit');
                $credit = (float) $this->lines($businessId, $start, $end)
                    ->where('jl.account_id', $account->id)
                    ->sum('jl.credit');

                return [
                    'code'            => $account->code,
                    'name'            => $account->name,
                    'type'            => $account->type,
                    'debit_total'     => round($debit, 2),
                    'credit_total'    => round($credit, 2),
                    'closing_balance' => round(abs($debit - $credit), 2),
                ];
            })
            ->values()
            ->all();

        $totalDebit = round(array_sum(array_column($rows, 'debit_total')), 2);
        $totalCredit = round(array_sum(array_column($rows, 'credit_total')), 2);

        return [
            'accounts'     => $rows,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'difference'   => round($totalDebit - $totalCredit, 2),
        ];
    }

    public function totalSales(int $businessId, string $start, string $end): float
    {
        return round((float) $this->lines($businessId, $start, $end)
            ->where('a.type', 'income')
            ->sum(DB::raw('jl.credit - jl.debit')), 2);
    }

    /**
     * Suppliers aging: open liability transactions bucketed by days outstanding.
     *
     * @return array{suppliers: array<int, array<string, mixed>>, totals: array<string, float>}
     */
    public function suppliersAging(int $businessId, string $asOf): array
    {
        $transactions = LedgerTransaction::query()
            ->where('business_id', $businessId)
            ->whereNull('parent_transaction_id')
            ->whereNotNull('supplier_id')
            ->whereIn('payment_status', ['pending', 'partially_paid'])
            ->whereDate('date', '<=', $asOf)
            ->with('supplier')
            ->get();

        $blank = [
            'current' => 0.0, 'days_1_30' => 0.0, 'days_31_60' => 0.0,
            'days_61_90' => 0.0, 'days_90_plus' => 0.0, 'total_due' => 0.0,
        ];

        $suppliers = [];
        $totals = $blank;
        $asOfDate = Carbon::parse($asOf)->startOfDay();

        foreach ($transactions as $txn) {
            $remaining = $txn->remainingAmount();
            if ($remaining <= 0) {
                continue;
            }

            $days = (int) Carbon::parse($txn->date)->startOfDay()->diffInDays($asOfDate);
            $bucket = match (true) {
                $days === 0 => 'current',
                $days <= 30 => 'days_1_30',
                $days <= 60 => 'days_31_60',
                $days <= 90 => 'days_61_90',
                default     => 'days_90_plus',
            };

            $sid = (int) $txn->supplier_id;
            if (! isset($suppliers[$sid])) {
                $suppliers[$sid] = array_merge(['supplier' => $txn->supplier?->name ?? 'Unknown'], $blank);
            }

            $suppliers[$sid][$bucket] += $remaining;
            $suppliers[$sid]['total_due'] += $remaining;
            $totals[$bucket] += $remaining;
            $totals['total_due'] += $remaining;
        }

        return [
            'suppliers' => array_values($suppliers),
            'totals'    => array_map(fn ($v) => round((float) $v, 2), $totals),
        ];
    }
}
