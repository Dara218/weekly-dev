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


class GetInboxController extends Controller
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
     * Get messages received by the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            abort_if(!$user instanceof User, Response::HTTP_UNAUTHORIZED);

            $messages = $this->getMessagesService->getInbox($user->id);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);
        } catch (\Exception $error) {
            LogService::error('Error fetching inbox.', [
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
