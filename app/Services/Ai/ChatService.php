<?php

namespace App\Services\Ai;

use App\Ai\Agents\SchoolAssistant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Ai\Responses\{
    AgentResponse,
    StreamableAgentResponse,
};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatService
{
    /**
     * Send a message and get a full JSON response.
     *
     * @param User $user
     * @param string $message
     * @param string|null $conversationId
     *
     * @return AgentResponse
     */
    public function sendMessage(User $user, string $message, ?string $conversationId = null): AgentResponse
    {
        $agent = $this->resolveAgent($user, $conversationId);

        return $agent->prompt($message);
    }

    /**
     * Send a message and get a streaming response (SSE).
     *
     * @param User $user
     * @param string $message
     * @param string|null $conversationId
     *
     * @return StreamableAgentResponse
     */
    public function streamMessage(User $user, string $message, ?string $conversationId = null): StreamableAgentResponse
    {
        $agent = $this->resolveAgent($user, $conversationId);

        return $agent->stream($message);
    }

    /**
     * List the user's AI conversations (most recent first).
     *
     * @param User $user
     * @param int|null $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getConversations(User $user, ?int $perPage = null)
    {
        $perPage ??= (int) config('constant.ai.conversations.default_per_page');

        return $user->conversations()
            ->latest('updated_at')
            ->paginate($perPage);
    }

    /**
     * Get one page of messages for a conversation.
     *
     * Page 1 is the latest page. Rows inside the page are newest-first;
     * the client sorts them oldest-first for display.
     *
     * @param User $user
     * @param string $conversationId
     * @param int|null $perPage
     * @param int|null $page
     * @throws NotFoundHttpException
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \Laravel\Ai\Models\ConversationMessage>
     */
    public function getConversationMessages(
        User $user,
        string $conversationId,
        ?int $perPage = null,
        ?int $page = null,
    ) {
        $perPage ??= (int) config('constant.ai.conversations.default_per_page');
        $page ??= (int) config('constant.ai.conversations.default_page');

        $conversation = $user->conversations()->find($conversationId);

        if ($conversation === null) {
            throw new NotFoundHttpException('Conversation not found.');
        }

        return $conversation->messages()
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Build the agent instance, optionally continuing an existing conversation.
     *
     * @param User $user
     * @param string|null $conversationId
     *
     * @return SchoolAssistant
     */
    private function resolveAgent(User $user, ?string $conversationId): SchoolAssistant
    {
        $agent = new SchoolAssistant();

        if ($conversationId !== null) {
            $this->assertConversationBelongsToUser($user, $conversationId);

            return $agent->continue($conversationId, as: $user);
        }

        return $agent->forUser($user);
    }

    /**
     * Verify the conversation belongs to the authenticated user.
     * The SDK does NOT do this automatically — your app must authorize.
     *
     * @param User $user
     * @param string $conversationId
     * @throws AuthorizationException
     *
     * @return void
     */
    private function assertConversationBelongsToUser(User $user, string $conversationId): void
    {
        $exists = $user->conversations()->where('id', $conversationId)->exists();

        if (!$exists) {
            throw new AuthorizationException('You do not have access to this conversation.');
        }
    }
}
