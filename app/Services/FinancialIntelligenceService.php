<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Debt;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialIntelligenceService
{
    /**
     * @return array<string, mixed>
     */
    public function analyze(User $user): array
    {
        $availableCash = $this->getAvailableCash($user);
        $monthlyExpectedIncome = (float) $user->incomeSources()
            ->where('is_active', true)
            ->get()
            ->sum(fn ($source): float => (float) $source->monthly_amount);

        $recurringExpenses = $user->expenses()
            ->where(function ($query): void {
                $query->where('is_recurring', true)
                    ->orWhere('frequency', '!=', 'one_time');
            })
            ->get();

        $monthlyRecurringExpenses = (float) $recurringExpenses
            ->sum(fn (Expense $expense): float => $this->toMonthlyAmount((float) $expense->amount, (string) $expense->frequency));

        $mandatoryMonthlyExpenses = (float) $recurringExpenses
            ->filter(fn (Expense $expense): bool => (bool) $expense->is_mandatory)
            ->sum(fn (Expense $expense): float => $this->toMonthlyAmount((float) $expense->amount, (string) $expense->frequency));

        $expectedIncomingReceivables = (float) $user->receivables()
            ->whereIn('status', ['pending', 'partially_paid'])
            ->get()
            ->sum(fn ($receivable): float => max(0, (float) $receivable->amount - (float) $receivable->amount_paid));

        $debts = $user->debts()
            ->where('status', 'active')
            ->get();

        $rankedDebts = $this->rankDebts(
            $debts,
            $availableCash,
            $monthlyExpectedIncome,
            $mandatoryMonthlyExpenses,
            $expectedIncomingReceivables,
        );

        return [
            'available_cash' => round($availableCash, 2),
            'monthly_expected_income' => round($monthlyExpectedIncome, 2),
            'monthly_recurring_expenses' => round($monthlyRecurringExpenses, 2),
            'monthly_mandatory_expenses' => round($mandatoryMonthlyExpenses, 2),
            'expected_incoming_receivables' => round($expectedIncomingReceivables, 2),
            'ranked_debts' => $rankedDebts,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recommendAllocation(User $user, float $incomeAmount): array
    {
        $incomeAmount = max(0, $incomeAmount);
        if ($incomeAmount <= 0) {
            return [];
        }

        $analysis = $this->analyze($user);
        $remaining = $incomeAmount;
        $allocations = [];

        foreach ($analysis['ranked_debts'] as $debt) {
            if (! in_array($debt['priority_status'], ['Critical', 'High Priority'], true)) {
                continue;
            }

            if ($remaining <= 0) {
                break;
            }

            $recommended = min((float) $debt['outstanding_balance'], $remaining);
            if ($recommended <= 0) {
                continue;
            }

            $allocations[] = [
                'type' => 'debt',
                'name' => $debt['creditor_name'],
                'reason' => $debt['priority_status'] . ' loan obligation',
                'amount' => round($recommended, 2),
            ];

            $remaining -= $recommended;
        }

        $mandatoryExpenses = $user->expenses()
            ->where('is_mandatory', true)
            ->where(function ($query): void {
                $query->where('is_recurring', true)
                    ->orWhere('frequency', '!=', 'one_time');
            })
            ->get()
            ->map(function (Expense $expense): array {
                $nextDueDate = $this->nextDueDateForExpense($expense);

                return [
                    'name' => $expense->name,
                    'next_due' => $nextDueDate,
                    'monthly_amount' => $this->toMonthlyAmount((float) $expense->amount, (string) $expense->frequency),
                ];
            })
            ->sortBy('next_due')
            ->values();

        foreach ($mandatoryExpenses as $expense) {
            if ($remaining <= 0) {
                break;
            }

            $recommended = min((float) $expense['monthly_amount'], $remaining);
            if ($recommended <= 0) {
                continue;
            }

            $allocations[] = [
                'type' => 'expense',
                'name' => $expense['name'],
                'reason' => 'Mandatory recurring expense',
                'amount' => round($recommended, 2),
            ];

            $remaining -= $recommended;
        }

        $reserveTarget = max(500.0, round((float) $analysis['monthly_mandatory_expenses'] * 0.15, 2));
        $reserve = min($reserveTarget, $remaining);

        if ($reserve > 0) {
            $allocations[] = [
                'type' => 'reserve',
                'name' => 'Emergency reserve',
                'reason' => 'Cash-flow safety buffer',
                'amount' => round($reserve, 2),
            ];

            $remaining -= $reserve;
        }

        if ($remaining > 0) {
            $allocations[] = [
                'type' => 'flex',
                'name' => 'Flexible allocation',
                'reason' => 'Optional extra debt prepayment or savings top-up',
                'amount' => round($remaining, 2),
            ];
        }

        return $allocations;
    }

    public function getAvailableCash(User $user): float
    {
        $accounts = (float) $user->accounts()->where('is_active', true)->sum('current_balance');
        $savings = (float) $user->savingsGoals()->where('status', 'active')->sum('current_amount');

        return max(0, $accounts + $savings);
    }

    /**
     * @param Collection<int, Debt> $debts
     * @return array<int, array<string, mixed>>
     */
    private function rankDebts(
        Collection $debts,
        float $availableCash,
        float $monthlyExpectedIncome,
        float $mandatoryMonthlyExpenses,
        float $expectedIncomingReceivables,
    ): array {
        return $debts
            ->map(function (Debt $debt) use ($availableCash, $monthlyExpectedIncome, $mandatoryMonthlyExpenses, $expectedIncomingReceivables): array {
                $monthlyObligation = $this->calculateMonthlyObligation($debt);
                $totalRepayment = $this->calculateTotalRepayment($debt);
                $interestPayable = max(0, $totalRepayment - (float) $debt->original_amount);

                $pctIncome = $monthlyExpectedIncome > 0
                    ? ($monthlyObligation / $monthlyExpectedIncome) * 100
                    : 100;

                $pctCash = $availableCash > 0
                    ? ((float) $debt->outstanding_balance / $availableCash) * 100
                    : 100;

                $remainingDisposable = ($monthlyExpectedIncome - $mandatoryMonthlyExpenses) - $monthlyObligation;
                $daysToDue = $debt->due_date
                    ? (int) round(now()->startOfDay()->diffInDays($debt->due_date->startOfDay(), false))
                    : null;

                $priorityScore = $this->calculatePriorityScore(
                    $daysToDue,
                    $debt,
                    $pctIncome,
                    $pctCash,
                    $availableCash,
                    $mandatoryMonthlyExpenses,
                    $expectedIncomingReceivables,
                );

                return [
                    'id' => $debt->id,
                    'creditor_name' => $debt->creditor_name,
                    'type' => $debt->type,
                    'outstanding_balance' => round((float) $debt->outstanding_balance, 2),
                    'due_date' => $debt->due_date,
                    'days_to_due' => $daysToDue,
                    'monthly_obligation' => round($monthlyObligation, 2),
                    'total_repayment_amount' => round($totalRepayment, 2),
                    'interest_payable' => round($interestPayable, 2),
                    'effective_interest_percentage' => round($this->effectiveInterest((float) $debt->original_amount, $totalRepayment), 2),
                    'income_percentage' => round($pctIncome, 2),
                    'cash_percentage' => round($pctCash, 2),
                    'disposable_after_payment' => round($remainingDisposable, 2),
                    'priority_score' => round($priorityScore, 2),
                    'priority_status' => $this->priorityStatus($priorityScore),
                    'affordability_status' => $this->affordabilityStatus($pctIncome, $pctCash, $remainingDisposable),
                ];
            })
            ->sortByDesc('priority_score')
            ->values()
            ->all();
    }

    private function calculatePriorityScore(
        ?int $daysToDue,
        Debt $debt,
        float $pctIncome,
        float $pctCash,
        float $availableCash,
        float $mandatoryMonthlyExpenses,
        float $expectedIncomingReceivables,
    ): float {
        $score = 0.0;

        if ($daysToDue !== null) {
            if ($daysToDue < 0) {
                $score += 45;
            } elseif ($daysToDue <= 7) {
                $score += 35;
            } elseif ($daysToDue <= 15) {
                $score += 25;
            } elseif ($daysToDue <= 30) {
                $score += 15;
            }
        } else {
            $score += 8;
        }

        $score += min(20.0, max(0.0, $pctCash * 0.2));

        $interest = (float) $debt->interest_rate;
        if ($interest >= 25) {
            $score += 15;
        } elseif ($interest >= 15) {
            $score += 10;
        } elseif ($interest >= 5) {
            $score += 5;
        }

        if ($pctIncome >= 45) {
            $score += 20;
        } elseif ($pctIncome >= 30) {
            $score += 12;
        } elseif ($pctIncome >= 15) {
            $score += 6;
        }

        $cashAfterEssentials = $availableCash - $mandatoryMonthlyExpenses;
        if ($cashAfterEssentials < 0) {
            $score += 20;
        } elseif ($cashAfterEssentials < $this->calculateMonthlyObligation($debt)) {
            $score += 10;
        }

        if ($expectedIncomingReceivables > 0) {
            $score -= min(10.0, ($expectedIncomingReceivables / max(1, (float) $debt->outstanding_balance)) * 5);
        }

        return max(0, $score);
    }

    private function priorityStatus(float $score): string
    {
        return match (true) {
            $score >= 70 => 'Critical',
            $score >= 50 => 'High Priority',
            $score >= 30 => 'Manageable',
            default => 'Low Priority',
        };
    }

    private function affordabilityStatus(float $pctIncome, float $pctCash, float $remainingDisposable): string
    {
        if ($pctIncome <= 20 && $pctCash <= 35 && $remainingDisposable >= 0) {
            return 'Affordable';
        }

        if ($pctIncome <= 35 && $pctCash <= 55 && $remainingDisposable >= -500) {
            return 'Borderline';
        }

        if ($pctIncome <= 50 && $pctCash <= 80) {
            return 'High Risk';
        }

        return 'Unaffordable';
    }

    private function calculateMonthlyObligation(Debt $debt): float
    {
        if ($debt->type === 'hire_purchase') {
            $details = (array) $debt->details;
            $remainingTermMonths = max((int) ($details['remaining_term_months'] ?? 0), 0);

            if ($remainingTermMonths <= 0) {
                $termMonths = max((int) ($details['term_months'] ?? 0), 0);
                $suggestedInstallment = (float) ($details['suggested_installment'] ?? 0);

                if ($termMonths > 0 && $suggestedInstallment > 0) {
                    $remainingTermMonths = max((int) ceil((float) $debt->outstanding_balance / $suggestedInstallment), 1);
                } else {
                    $remainingTermMonths = $termMonths;
                }
            }

            if ($remainingTermMonths > 0 && (float) $debt->outstanding_balance > 0) {
                return (float) $debt->outstanding_balance / $remainingTermMonths;
            }
        }

        $installment = (float) $debt->monthly_installment;

        if ($installment > 0) {
            return match ($debt->repayment_frequency) {
                'daily' => $installment * 30,
                'weekly' => $installment * 4.33,
                'bi_weekly' => $installment * 2.17,
                default => $installment,
            };
        }

        if (! $debt->due_date || (float) $debt->outstanding_balance <= 0) {
            return 0;
        }

        $monthsRemaining = max(1, (int) ceil(max(1, now()->diffInDays($debt->due_date, false)) / 30));

        return (float) $debt->outstanding_balance / $monthsRemaining;
    }

    private function calculateTotalRepayment(Debt $debt): float
    {
        if ((float) $debt->total_repayment_amount > 0) {
            return (float) $debt->total_repayment_amount;
        }

        return (float) $debt->original_amount * (1 + ((float) $debt->interest_rate / 100));
    }

    private function effectiveInterest(float $principal, float $totalRepayment): float
    {
        if ($principal <= 0 || $totalRepayment <= 0) {
            return 0;
        }

        return (($totalRepayment - $principal) / $principal) * 100;
    }

    private function toMonthlyAmount(float $amount, string $frequency): float
    {
        return match ($frequency) {
            'daily' => $amount * 30,
            'weekly' => $amount * 4.33,
            'bi_weekly' => $amount * 2.17,
            'monthly' => $amount,
            'quarterly' => $amount / 3,
            'annually' => $amount / 12,
            default => $amount,
        };
    }

    private function nextDueDateForExpense(Expense $expense): Carbon
    {
        $baseDate = $expense->expense_date ?? now();

        return match ($expense->frequency) {
            'daily' => $baseDate->copy()->addDay(),
            'weekly' => $baseDate->copy()->addWeek(),
            'bi_weekly' => $baseDate->copy()->addWeeks(2),
            'monthly' => $baseDate->copy()->addMonth(),
            'quarterly' => $baseDate->copy()->addMonths(3),
            'annually' => $baseDate->copy()->addYear(),
            default => $baseDate->copy(),
        };
    }
}
