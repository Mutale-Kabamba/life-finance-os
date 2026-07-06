<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailTestCommand extends Command
{
<<<<<<< HEAD
    protected $signature = 'mail:test {email} {--from=} {--verify : Send a real email verification notification to an existing user}';
=======
    protected $signature = 'mail:test {email} {--from=} {--verify}';
>>>>>>> origin/main

    protected $description = 'Send a test email (or a real verification email with --verify) to check mail delivery on the current environment.';

    public function handle(): int
    {
        $to = (string) $this->argument('email');
        $from = $this->option('from') ?: config('mail.from.address');
        $verify = (bool) $this->option('verify');

<<<<<<< HEAD
        $this->line('Mailer: '.config('mail.default'));
        $this->line('Host:   '.config('mail.mailers.smtp.host'));
        $this->line('Port:   '.config('mail.mailers.smtp.port'));
        $this->line('Scheme: '.(config('mail.mailers.smtp.scheme') ?: 'null'));
        $this->line('App URL: '.config('app.url'));
        $this->line('Queue:   '.config('queue.default'));
        $this->line('From:   '.$from);
        $this->line('To:     '.$to);
        $this->line('Mode:   '.($verify ? 'verify notification' : 'smtp raw test'));
        $this->newLine();

        if ($verify) {
            /** @var User|null $user */
            $user = User::query()->where('email', $to)->first();

            if (! $user) {
                $this->error('No user found with that email.');
                $this->line('Tip: create/register the user first, then retry with --verify.');

                return self::FAILURE;
            }

            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $e) {
                $this->error('FAILED to send verification notification.');
                $this->error(get_class($e).': '.$e->getMessage());

                return self::FAILURE;
            }

            $this->info('Verification notification dispatched successfully. Check inbox (and spam).');

            return self::SUCCESS;
=======
        $this->line('Mailer:  '.config('mail.default'));
        $this->line('Host:    '.config('mail.mailers.smtp.host'));
        $this->line('Port:    '.config('mail.mailers.smtp.port'));
        $this->line('Scheme:  '.(config('mail.mailers.smtp.scheme') ?: 'null'));
        $this->line('App URL: '.config('app.url'));
        $this->line('Queue:   '.config('queue.default'));
        $this->line('From:    '.$from);
        $this->line('To:      '.$to);
        $this->newLine();

        if ($this->option('verify')) {
            return $this->sendVerification($to);
>>>>>>> origin/main
        }

        try {
            Mail::raw('Life Finance OS SMTP test message sent at '.now()->toDateTimeString(), function ($message) use ($to, $from) {
                $message->to($to)
                    ->from($from, config('mail.from.name'))
                    ->subject('Life Finance OS - SMTP Test');
            });
        } catch (Throwable $e) {
            $this->error('FAILED to send email.');
            $this->error(get_class($e).': '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Test email dispatched successfully. Check the inbox (and spam).');

        return self::SUCCESS;
    }

    private function sendVerification(string $email): int
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email {$email}.");

            return self::FAILURE;
        }

        if ($user->hasVerifiedEmail()) {
            $this->warn('This user is already verified. Sending the notification anyway for testing.');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            $this->error('FAILED to send verification email.');
            $this->error(get_class($e).': '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Verification email dispatched successfully. Check the inbox (and spam).');

        return self::SUCCESS;
    }
}
