<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FinancialCalendarEventBuilder;
use Illuminate\Console\Command;

class BackfillFinancialCalendarCommand extends Command
{
    protected $signature = 'financial:calendar-backfill {--user_id=}';

    protected $description = 'Backfill Financial Calendar entries from debts, receivables, recurring expenses, invoices, savings goals, and payroll runs.';

    public function handle(FinancialCalendarEventBuilder $builder): int
    {
        $userId = $this->option('user_id');

        $users = User::query()
            ->when($userId, fn ($query) => $query->whereKey($userId))
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No matching users found.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $builder->rebuildForUser($user);
            $this->info('Calendar reminders built for user #' . $user->id . ' (' . $user->email . ')');
        }

        $this->info('Financial calendar backfill complete.');

        return self::SUCCESS;
    }
}
