<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {email} {--from=}';

    protected $description = 'Send a test email to verify SMTP configuration on the current environment.';

    public function handle(): int
    {
        $to = (string) $this->argument('email');
        $from = $this->option('from') ?: config('mail.from.address');

        $this->line('Mailer: '.config('mail.default'));
        $this->line('Host:   '.config('mail.mailers.smtp.host'));
        $this->line('Port:   '.config('mail.mailers.smtp.port'));
        $this->line('Scheme: '.(config('mail.mailers.smtp.scheme') ?: 'null'));
        $this->line('From:   '.$from);
        $this->line('To:     '.$to);
        $this->newLine();

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
}
