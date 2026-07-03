<?php

namespace App\Console\Commands;

use App\Models\FinancialCalendar;
use App\Notifications\FinancialCalendarReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendFinancialCalendarRemindersCommand extends Command
{
    protected $signature = 'financial:send-reminders {--user_id=}';

    protected $description = 'Send due and upcoming financial calendar reminders by email.';

    public function handle(): int
    {
        $today = Carbon::today();
        $userId = $this->option('user_id');
        $sent = 0;

        FinancialCalendar::query()
            ->with('user')
            ->where('notify_before', true)
            ->where('is_completed', false)
            ->whereNotNull('due_date')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('due_date')
            ->chunkById(200, function ($events) use ($today, &$sent): void {
                foreach ($events as $event) {
                    if (! $event->user) {
                        continue;
                    }

                    $daysUntilDue = $today->diffInDays(Carbon::parse($event->due_date), false);
                    $notifyWindow = (int) $event->notify_days_before;

                    // Send while in the pre-due window and for overdue events.
                    if ($daysUntilDue > $notifyWindow) {
                        continue;
                    }

                    $dailyKey = sprintf(
                        'financial-reminder:%d:%s',
                        $event->id,
                        $today->toDateString(),
                    );

                    if (! Cache::add($dailyKey, true, now()->addDay())) {
                        continue;
                    }

                    $event->user->notify(new FinancialCalendarReminder($event, $daysUntilDue));
                    $sent++;
                }
            });

        $this->info('Financial reminders sent: ' . $sent);

        return self::SUCCESS;
    }
}
