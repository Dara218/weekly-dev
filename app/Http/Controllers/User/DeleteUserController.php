<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Interfaces\UserInterface;
use App\Services\Common\LogService;
use Illuminate\Http\{
    Request,
    Response,
};

class DeleteUserController extends Controller
{
    /**
     * Repository interface for user data operations.
     *
     * @var \App\Interfaces\UserInterface
     */
    protected UserInterface $userInterface;

    /**
     * Initialize the service.
     *
     * @param \App\Interfaces\UserInterface $userInterface
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Delete a single user.
     *
     * @param int $id The user id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(int $id): Response
    {
        try {
            $this->userInterface->find($id)->delete();

            return response([
                'success' => true,
                'message' => 'User deleted successfully.',
            ], Response::HTTP_OK);
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

    /**
     * Delete bulk users.
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request): Response
    {
        try {
            $ids = $request->ids;

            $this->userInterface->delete($ids);

            return response([
                'success' => true,
                'message' => 'Users deleted successfully.',
            ]);
        } catch (\Exception $error) {
            LogService::error('Error deleting users.', [
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
