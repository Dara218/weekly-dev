<?php

namespace App\Services\Message;

use App\Interfaces\MessageInterface;
use Illuminate\Database\Eloquent\Collection;

class GetMessagesService
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
     * Get messages received by the given user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message>
     */
    public function getInbox(int $userId): Collection
    {
        return $this->messageRepository->getInboxForUser($userId);
    }

    /**
     * Get messages between two users.
     *
     * @param int $userId
     * @param int $otherUserId
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message>
     */
    public function getConversation(int $userId, int $otherUserId): Collection
    {
        return $this->messageRepository->getConversation($userId, $otherUserId);
    }
}
