<?php

namespace App\Repositories;

use App\Interfaces\MessageInterface;
use App\Models\Message;
use Illuminate\Database\Eloquent\{
    Collection,
    Model,
};

class MessageRepository extends BaseRepository implements MessageInterface
{
    /**
     * The Model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected Model $model;

    /**
     * Constructor for initializing the BaseRepository.
     *
     * @param \App\Models\Message $model
     */
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getInboxForUser(int $userId): Collection
    {
        return Message::query()
            ->where('receiver_id', $userId)
            ->with(['sender:id,first_name,last_name,profile_image'])
            ->latest('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getConversation(int $userId, int $otherUserId): Collection
    {
        return Message::query()
            ->where(function ($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                    ->where('receiver_id', $userId);
            })
            ->with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Message
    {
        $data['created_at'] = now();

        return Message::query()->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function markAsRead(int $messageId): Message
    {
        $message = Message::query()->findOrFail($messageId);
        $message->update(['read_at' => now()]);

        return $message;
    }
}
