<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Services\Authentication\LoginService;
use App\Services\Common\LogService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class LoginController extends Controller
{
    /**
     * Initialize LoginService instance.
     *
     * @var \App\Services\Authentication\LoginService $loginService
     */
    protected LoginService $loginService;

    /**
     * Initialize UserService instance.
     *
     * @var \App\Services\User\UserService $userService
     */
    protected UserService $userService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\Authentication\LoginService $loginService
     * @param \App\Services\User\UserService $userService
     */
    public function __construct(LoginService $loginService, UserService $userService)
    {
        $this->loginService = $loginService;
        $this->userService = $userService;
    }

    /**
     * Login the user.
     *
     * @param \App\Http\Requests\Authentication\LoginRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(LoginRequest $request): Response
    {
        try {
            $loginDetails = $request->validated();

            $this->loginService->handleLogin($loginDetails);

            $userId = Auth::id();

            if ($userId === null) {
                return response([
                    'success' => false,
                    'message' => 'Unable to determine authenticated user.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $data = $this->userService->loadUserProfile((int) $userId);

            return response([
                'success' => true,
                'data' => $data,
            ]);
        } catch (UnprocessableEntityHttpException $error) {
            LogService::error('Error logging-in user.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $error) {
            LogService::error('Error logging-in user.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => 'Internal server error. Try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout the user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request): Response
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);
        } catch (\Exception $error) {
            LogService::error('Error logging-out user.', [
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
