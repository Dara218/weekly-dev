<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\SendChatMessageRequest;
use App\Models\User;
use App\Services\Ai\ChatService;
use App\Services\Common\LogService;
use Illuminate\Http\Response;

class StreamChatMessageController extends Controller
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
     * Send a chat message and stream the reply as server-sent events.
     *
     * @param \App\Http\Requests\Ai\SendChatMessageRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function __invoke(SendChatMessageRequest $request)
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $streamable = $this->chatService->streamMessage(
                $user,
                $request->validated('message'),
                $request->validated('conversation_id'),
            );

            return response()->stream(function () use ($streamable) {
                foreach ($streamable as $event) {
                    echo 'data: ' . ((string) $event) . "\n\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                echo "data: [DONE]\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        } catch (\Exception $error) {
            LogService::error('Error streaming AI chat message.', [
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
