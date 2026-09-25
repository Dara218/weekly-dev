<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Models\User;
use App\Services\Common\LogService;
use App\Services\Message\SendMessageService;
use Illuminate\Http\{
    JsonResponse,
    Response,
};

class SendMessageController extends Controller
{
    /**
     * Initialize classes.
     *
     * @param \App\Services\Message\SendMessageService $sendMessageService
     */
    public function __construct(
        private readonly SendMessageService $sendMessageService,
    ) {
    }

    /**
     * Send a message to another user.
     *
     * @param \App\Http\Requests\Message\SendMessageRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(SendMessageRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $message = $this->sendMessageService->handle(
                $user,
                $request->validated('receiver_id'),
                $request->validated('body'),
                $request->validated('attachment'),
            );

            return response()->json([
                'success' => true,
                'data' => $message,
            ], Response::HTTP_CREATED);
        } catch (\Exception $error) {
            LogService::error('Error sending message.', [
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
