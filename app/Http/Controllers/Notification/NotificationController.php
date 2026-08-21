<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Common\LogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the authenticated user's unread notifications count.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'count' => $user->unread_notifications_count,
        ]);
    }

    /**
     * Get the authenticated user's unread notifications.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'unread' => $user->unreadNotifications,
        ]);
    }

    /**
     * Mark all of the authenticated user's unread notifications as read.
     *
     * @return \Illuminate\Http\Response
     */
    public function readAllNotification(): Response
    {
        try {
            $user = Auth::user();

            if (!$user instanceof User) {
                abort(Response::HTTP_UNAUTHORIZED);
            }

            $user->unreadNotifications->markAsRead();

            return response(['success' => true]);
        } catch (\Exception $error) {
            LogService::error('Error fetching user data.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
