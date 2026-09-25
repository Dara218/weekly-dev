<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\{
    Message,
    User,
};

class MessagePolicy
{
    /**
     * Determine if the sender may message the receiver.
     *
     * @param \App\Models\User $sender
     * @param int $receiverId
     *
     * @return bool
     */
    public function send(User $sender, int $receiverId): bool
    {
        if ($sender->id === $receiverId) {
            return false;
        }

        $receiver = User::find($receiverId);

        if ($receiver === null) {
            return false;
        }

        return match (true) {
            $sender->role === UserRole::ADMIN->value => true,
            $sender->role === UserRole::PARENT->value && $receiver->role === UserRole::TEACHER->value => true,
            $sender->role === UserRole::TEACHER->value && $receiver->role === UserRole::PARENT->value => true,
            default => false,
        };
    }

    /**
     * Determine if the user may view the message.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Message $message
     *
     * @return bool
     */
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->id === $message->receiver_id;
    }
}
