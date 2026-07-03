<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Expense;
use App\Models\FinancialCalendar;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\Receivable;
use App\Models\SavingsGoal;
use App\Models\User;

class FinancialCalendarEventBuilder
{
    public function rebuildForUser(User $user): void
    {
        $this->syncDebts($user);
        $this->syncReceivables($user);
        $this->syncRecurringExpenses($user);
        $this->syncInvoices($user);
        $this->syncSavingsGoals($user);
        $this->syncPayrollRuns($user);
    }

    private function syncDebts(User $user): void
    {
        $user->debts()
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->get()
            ->each(function (Debt $debt) use ($user): void {
                $this->upsertCalendarEvent(
                    $user,
                    Debt::class,
                    $debt->id,
                    [
                        'title' => 'Loan payment due: ' . $debt->creditor_name,
                        'description' => 'Outstanding: ZMW ' . number_format((float) $debt->outstanding_balance, 2),
                        'type' => 'loan_payment',
                        'source_label' => $debt->creditor_name,
                        'due_date' => $debt->due_date,
                        'amount' => $debt->outstanding_balance,
                        'is_recurring' => ! empty($debt->repayment_frequency),
                        'recurrence_pattern' => $debt->repayment_frequency,
                        'notify_before' => true,
                        'notify_days_before' => 3,
                        'is_completed' => (float) $debt->outstanding_balance <= 0,
                    ]
                );
            });
    }

    private function syncReceivables(User $user): void
    {
        $user->receivables()
            ->whereIn('status', ['pending', 'partially_paid'])
            ->whereNotNull('due_date')
            ->get()
            ->each(function (Receivable $receivable) use ($user): void {
                $this->upsertCalendarEvent(
                    $user,
                    Receivable::class,
                    $receivable->id,
                    [
                        'title' => 'Collection expected: ' . $receivable->debtor_name,
                        'description' => 'Outstanding: ZMW ' . number_format((float) $receivable->outstanding, 2),
                        'type' => 'customer_payment',
                        'source_label' => $receivable->debtor_name,
                        'due_date' => $receivable->due_date,
                        'amount' => $receivable->outstanding,
                        'is_recurring' => false,
                        'recurrence_pattern' => null,
                        'notify_before' => true,
                        'notify_days_before' => 2,
                        'is_completed' => $receivable->status === 'paid',
                    ]
                );
            });
    }

    private function syncRecurringExpenses(User $user): void
    {
        $user->expenses()
            ->where(function ($query): void {
                $query->where('is_recurring', true)
                    ->orWhere('frequency', '!=', 'one_time');
            })
            ->get()
            ->each(function (Expense $expense) use ($user): void {
                $dueDate = $expense->expense_date ?? now();

                $this->upsertCalendarEvent(
                    $user,
                    Expense::class,
                    $expense->id,
                    [
                        'title' => 'Expense due: ' . $expense->name,
                        'description' => 'Recurring expense obligation',
                        'type' => 'bill',
                        'source_label' => $expense->name,
                        'due_date' => $dueDate,
                        'amount' => $expense->amount,
                        'is_recurring' => true,
                        'recurrence_pattern' => $expense->frequency,
                        'notify_before' => true,
                        'notify_days_before' => (bool) $expense->is_mandatory ? 5 : 2,
                        'is_completed' => false,
                    ]
                );
            });
    }

    private function syncInvoices(User $user): void
    {
        Invoice::query()
            ->whereHas('business', fn ($query) => $query->where('user_id', $user->id))
            ->whereNotNull('due_date')
            ->where('status', '!=', 'paid')
            ->get()
            ->each(function (Invoice $invoice) use ($user): void {
                $this->upsertCalendarEvent(
                    $user,
                    Invoice::class,
                    $invoice->id,
                    [
                        'title' => 'Invoice due: ' . $invoice->invoice_number,
                        'description' => 'Balance due: ZMW ' . number_format((float) $invoice->balance_due, 2),
                        'type' => 'customer_payment',
                        'source_label' => $invoice->invoice_number,
                        'due_date' => $invoice->due_date,
                        'amount' => $invoice->balance_due,
                        'is_recurring' => false,
                        'recurrence_pattern' => null,
                        'notify_before' => true,
                        'notify_days_before' => 2,
                        'is_completed' => $invoice->status === 'paid',
                    ]
                );
            });
    }

    private function syncSavingsGoals(User $user): void
    {
        $user->savingsGoals()
            ->where('status', 'active')
            ->whereNotNull('target_date')
            ->get()
            ->each(function (SavingsGoal $goal) use ($user): void {
                $remaining = max(0, (float) $goal->target_amount - (float) $goal->current_amount);

                $this->upsertCalendarEvent(
                    $user,
                    SavingsGoal::class,
                    $goal->id,
                    [
                        'title' => 'Savings target: ' . $goal->name,
                        'description' => 'Remaining: ZMW ' . number_format($remaining, 2),
                        'type' => 'savings_target',
                        'source_label' => $goal->name,
                        'due_date' => $goal->target_date,
                        'amount' => $remaining,
                        'is_recurring' => false,
                        'recurrence_pattern' => null,
                        'notify_before' => true,
                        'notify_days_before' => 7,
                        'is_completed' => $remaining <= 0,
                    ]
                );
            });
    }

    private function syncPayrollRuns(User $user): void
    {
        PayrollRun::query()
            ->whereHas('business', fn ($query) => $query->where('user_id', $user->id))
            ->whereNotNull('payment_date')
            ->whereIn('status', ['pending', 'processed'])
            ->get()
            ->each(function (PayrollRun $payrollRun) use ($user): void {
                $this->upsertCalendarEvent(
                    $user,
                    PayrollRun::class,
                    $payrollRun->id,
                    [
                        'title' => 'Payroll payment: ' . $payrollRun->name,
                        'description' => 'Total net payout: ZMW ' . number_format((float) $payrollRun->total_net, 2),
                        'type' => 'payroll',
                        'source_label' => $payrollRun->name,
                        'due_date' => $payrollRun->payment_date,
                        'amount' => $payrollRun->total_net,
                        'is_recurring' => false,
                        'recurrence_pattern' => null,
                        'notify_before' => true,
                        'notify_days_before' => 1,
                        'is_completed' => $payrollRun->status === 'completed',
                    ]
                );
            });
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function upsertCalendarEvent(User $user, string $sourceType, int $sourceId, array $attributes): void
    {
        FinancialCalendar::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ],
            $attributes,
        );
    }
}
