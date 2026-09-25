<?php

namespace App\Services\Message;

use App\Events\MessageSent;
use App\Interfaces\MessageInterface;
use App\Models\{
    Message,
    User,
};
use Illuminate\Support\Facades\Gate;

class SendMessageService
{
    /**
     * Initialize classes.
     *
     * @param \App\Interfaces\MessageInterface $messageRepository
     */
    public function __construct(
        private readonly MessageInterface $messageRepository,
    ) {
    }

    /**
     * Store a message and broadcast it to the receiver.
     *
     * @param \App\Models\User $sender
     * @param int $receiverId
     * @param string $body
     * @param string|null $attachment
     *
     * @return \App\Models\Message
     */
    public function handle(User $sender, int $receiverId, string $body, ?string $attachment = null): Message
    {
        Gate::forUser($sender)->authorize('send', [Message::class, $receiverId]);

        $message = $this->messageRepository->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'body' => $body,
            'attachment' => $attachment,
        ]);

        MessageSent::dispatch($message);

        return $message->load(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name']);
    }
}
