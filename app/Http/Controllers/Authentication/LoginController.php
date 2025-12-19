<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Services\Authentication\LoginService;
use App\Services\Common\LogService;
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
     * Initialize classes.
     *
     * @param \App\Services\Authentication\LoginService $loginService
     */
    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * Login the user.
     *
     * @param \App\Http\Requests\Authentication\LoginRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(LoginRequest $request)
    {
        try {
            $loginDetails = $request->validated();

            $this->loginService->handleLogin($loginDetails);

            return response([
                'success' => true,
                'data' => Auth::user(),
            ]);
        } catch (UnprocessableEntityHttpException $error) {
            LogService::error('Error logging-in user.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => $error->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $error) {
            LogService::error('Error logging-in user.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => $error->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
