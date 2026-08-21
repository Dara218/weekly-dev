<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TeacherProfileUpdated extends Notification
{
    use Queueable;

    /**
     * @var array<string, mixed>
     */
    protected array $changes;

    /**
     * Create a new notification instance.
     *
     * @param array<string, mixed> $changes
     */
    public function __construct(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    /**
     * Get the array representation of the notification. Data that will be saved to database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Profile Updated',
            'message' => 'An admin has updated your profile.',
            'changes' => $this->changes,
            // 'notification_unread_count' => $user
        ];
    }

    /**
     * Get the array representation of the notification. Data that will be sent over Reverb to the browser in real time.
     *
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Profile Updated',
            'message' => 'An admin has updated your profile.',
            'changes' => $this->changes,
        ]);
    }
}
