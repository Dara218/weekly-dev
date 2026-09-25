<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\SendChatMessageRequest;
use App\Models\User;
use App\Services\Ai\ChatService;
use App\Services\Common\LogService;
use Illuminate\Http\{
    JsonResponse,
    Response,
};

class SendChatMessageController extends Controller
{
    /**
     * Initialize classes.
     *
     * @param ChatService $chatService
     */
    public function __construct(
        private readonly ChatService $chatService,
    ) {
    }

    /**
     * Send a chat message and return the full reply.
     *
     * @param \App\Http\Requests\Ai\SendChatMessageRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(SendChatMessageRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $response = $this->chatService->sendMessage(
                $user,
                $request->validated('message'),
                $request->validated('conversation_id'),
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'reply' => (string) $response,
                    'conversation_id' => $response->conversationId,
                ],
            ]);
        } catch (\Exception $error) {
            LogService::error('Error sending AI chat message.', [
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
