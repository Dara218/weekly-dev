<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Common\LogService;
use App\Services\Message\GetMessagesService;
use Illuminate\Http\{
    JsonResponse,
    Request,
    Response,
};

class GetConversationController extends Controller
{
    /**
     * Initialize classes.
     *
     * @param \App\Services\Message\GetMessagesService $getMessagesService
     */
    public function __construct(
        private readonly GetMessagesService $getMessagesService,
    ) {
    }

    /**
     * Get the conversation between the authenticated user and another user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, int $userId): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $messages = $this->getMessagesService->getConversation($user->id, $userId);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching conversation.', [
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
