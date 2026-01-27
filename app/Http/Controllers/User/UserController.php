<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Common\LogService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Initialize UserService instance.
     *
     * @var \App\Services\User\UserService $userService
     */
    protected UserService $userService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\User\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get the user data.
     *
     * @return mixed|\Illuminate\Http\Response
     */
    public function getUser()
    {
        try {
            $userId = Auth::id();

            if ($userId === null) {
                return response([
                    'success' => false,
                    'message' => 'Unable to determine authenticated user.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->userService->loadUserProfile((int) $userId);
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
