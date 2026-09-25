<?php

namespace App\Interfaces;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageInterface
{
    /**
     * Get messages received by the given user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message>
     */
    public function getInboxForUser(int $userId): Collection;

    /**
     * Get messages between two users.
     *
     * @param int $userId
     * @param int $otherUserId
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message>
     */
    public function getConversation(int $userId, int $otherUserId): Collection;

    /**
     * Store a new message.
     *
     * @param array<string, mixed> $data
     *
     * @return \App\Models\Message
     */
    public function create(array $data): Message;

    /**
     * Mark a message as read.
     *
     * @param int $messageId
     *
     * @return \App\Models\Message
     */
    public function markAsRead(int $messageId): Message;
}
