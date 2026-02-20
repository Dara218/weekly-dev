<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\Common\LogService;
use App\Services\User\UpdateUserService;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserController extends Controller
{
    /**
     * Initialize UpdateUserService instance.
     *
     * @var \App\Services\User\UpdateUserService $updateUserService
     */
    protected UpdateUserService $updateUserService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\User\UpdateUserService $updateUserService
     */
    public function __construct(UpdateUserService $updateUserService)
    {
        $this->updateUserService = $updateUserService;
    }

    /**
     * Update the user.
     *
     * @param \App\Http\Requests\User\UpdateUserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(int $id, UpdateUserRequest $request): Response
    {
        try {
            $data = $this->updateUserService
                ->handleUpdateUser($id, $request->validated());

            return response([
                'success' => true,
                'message' => 'Student updated successfully.',
                'data' => $data,
            ]);
        } catch (Exception $error) {
            LogService::error('Error updating a student.', [
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
