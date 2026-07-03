<?php

namespace App\Notifications;

use App\Models\FinancialCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinancialCalendarReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly FinancialCalendar $event,
        private readonly int $daysUntilDue,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subjectPrefix = $this->daysUntilDue < 0 ? 'Overdue' : 'Reminder';
        $when = $this->humanDueLabel();

        $mail = (new MailMessage())
            ->subject($subjectPrefix . ': ' . $this->event->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->event->title)
            ->line('When: ' . $when)
            ->line('Type: ' . ucfirst(str_replace('_', ' ', (string) $this->event->type)));

        if ($this->event->amount !== null) {
            $mail->line('Amount: ZMW ' . number_format((float) $this->event->amount, 2));
        }

        if (! empty($this->event->description)) {
            $mail->line('Details: ' . $this->event->description);
        }

        return $mail
            ->action('Open Dashboard', url('/admin'))
            ->line('You are receiving this because reminders are enabled for this calendar item.');
    }

    private function humanDueLabel(): string
    {
        if ($this->daysUntilDue < 0) {
            $daysOverdue = abs($this->daysUntilDue);

            return $this->event->due_date?->format('M d, Y') . ' (' . $daysOverdue . ' day' . ($daysOverdue === 1 ? '' : 's') . ' overdue)';
        }

        if ($this->daysUntilDue === 0) {
            return $this->event->due_date?->format('M d, Y') . ' (due today)';
        }

        return $this->event->due_date?->format('M d, Y') . ' (in ' . $this->daysUntilDue . ' day' . ($this->daysUntilDue === 1 ? '' : 's') . ')';
    }
}
