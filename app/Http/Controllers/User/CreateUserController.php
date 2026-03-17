<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\{
    BulkCreateUserRequest,
    CreateUserRequest,
};
use App\Services\Common\LogService;
use App\Services\User\CreateUserService;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class CreateUserController extends Controller
{
    /**
     * Initialize CreateUserService instance.
     *
     * @var \App\Services\User\CreateUserService $createUserService
     */
    protected CreateUserService $createUserService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\User\CreateUserService $createUserService
     */
    public function __construct(CreateUserService $createUserService)
    {
        $this->createUserService = $createUserService;
    }

    /**
     * Create a new user.
     *
     * @param \App\Http\Requests\User\CreateUserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserRequest $request)
    {
        try {
            $data = $this->createUserService->handleCreateUser($request->validated());

            return response([
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => $data,
            ]);
        } catch (Exception $error) {
            LogService::error('Error creating a student.', [
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
     * Create new users.
     *
     * @param BulkCreateUserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(BulkCreateUserRequest $request): Response
    {
        try {
            $this->createUserService
                ->handleBulkCreateUser($request->validated('rows'));

            return response([
                'success' => true,
                'message' => 'Students created successfully.',
            ]);
        } catch (Exception $error) {
            LogService::error('Error creating students', [
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
