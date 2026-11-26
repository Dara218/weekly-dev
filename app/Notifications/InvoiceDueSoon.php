<?php

namespace App\Notifications;

use App\Models\{
    Invoice,
    Student,
    User,
};
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\{
    BroadcastMessage,
    MailMessage,
};
use Illuminate\Notifications\Notification;

class InvoiceDueSoon extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Invoice $invoice The unpaid invoice (with student.user loaded).
     */
    public function __construct(
        private readonly Invoice $invoice,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable The recipient.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Build the reminder email for the recipient.
     *
     * @param object $notifiable The recipient.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $payload = $this->payload();

        return (new MailMessage())
            ->subject($payload['title'])
            ->greeting('Hello ' . ($notifiable instanceof User ? $notifiable->first_name : '') . ',')
            ->line($payload['message'])
            ->action('Pay school fees', $payload['url']);
    }

    /**
     * Data stored in the notifications table.
     *
     * @param object $notifiable The recipient.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /**
     * Data pushed to the bell panel in real time.
     *
     * @param object $notifiable The recipient.
     *
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    /**
     * Shared payload for database, broadcast, and mail.
     *
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        /** @var Student|null $student */
        $student = $this->invoice->student;
        $user = $student?->user;
        $studentName = $user instanceof User
            ? trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name])))
            : '';

        return [
            'title' => 'Tuition due in 3 days',
            'message' => sprintf(
                'Invoice %s%s is due on %s. Amount due: ₱%s.',
                $this->invoice->invoice_no,
                $studentName === '' ? '' : ' for ' . $studentName,
                $this->invoice->due_date,
                $this->invoice->total_amount,
            ),
            'invoice_id' => $this->invoice->id,
            'invoice_no' => $this->invoice->invoice_no,
            'due_date' => (string) $this->invoice->due_date,
            'url' => config('services.frontend.url') . '/fees',
        ];
    }
}
