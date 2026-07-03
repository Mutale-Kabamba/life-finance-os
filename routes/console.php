<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Services\FinancialCalendarEventBuilder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('financial:calendar-backfill {--user_id=}', function () {
    $builder = app(FinancialCalendarEventBuilder::class);
    $userId = $this->option('user_id');

    $users = User::query()
        ->when($userId, fn ($query) => $query->whereKey($userId))
        ->get();

    if ($users->isEmpty()) {
        $this->warn('No matching users found.');

        return;
    }

    foreach ($users as $user) {
        $builder->rebuildForUser($user);
        $this->info('Calendar reminders built for user #' . $user->id . ' (' . $user->email . ')');
    }

    $this->info('Financial calendar backfill complete.');
})->purpose('Backfill Financial Calendar reminders from financial due-date records.');

Schedule::command('financial:calendar-backfill')->dailyAt('06:45');
Schedule::command('financial:send-reminders')->dailyAt('07:00');
