<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ai\ChatService;
use App\Services\Common\LogService;
use Illuminate\Http\{
    JsonResponse,
    Request,
    Response
};

class GetConversationsController extends Controller
{
    /**
     * Initialize classes.
     *
     * @param \App\Services\Ai\ChatService $chatService
     */
    public function __construct(
        private readonly ChatService $chatService,
    ) {
    }

    /**
     * List the authenticated user's AI conversations.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $conversations = $this->chatService->getConversations($user);

            return response()->json([
                'success' => true,
                'data' => $conversations,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching AI conversations.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get one page of messages for a conversation.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $conversationId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $conversationId): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $perPage = min(
                max(
                    (int) $request->input('per_page', config('constant.ai.conversations.default_per_page')),
                    config('constant.ai.conversations.min_per_page'),
                ),
                config('constant.ai.conversations.max_per_page'),
            );
            $page = max(
                (int) $request->input('page', config('constant.ai.conversations.default_page')),
                config('constant.ai.conversations.default_page'),
            );

            $messages = $this->chatService->getConversationMessages(
                $user,
                $conversationId,
                $perPage,
                $page,
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'messages' => $messages,
                ],
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching AI conversation messages.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
