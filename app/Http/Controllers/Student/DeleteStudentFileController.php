<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Common\LogService;
use App\Services\User\DeleteFileService;
use Illuminate\Http\Response;

class DeleteStudentFileController extends Controller
{
    /**
     * Initialize DeleteFileService instance.
     *
     * @var \App\Services\User\DeleteFileService $deleteFileService
     */
    protected DeleteFileService $deleteFileService;

    /**
     * Initialize the controller.
     *
     * @param \App\Services\User\DeleteFileService $deleteFileService
     */
    public function __construct(DeleteFileService $deleteFileService)
    {
        $this->deleteFileService = $deleteFileService;
    }

    /**
     * Upload the file in the storage.
     *
     * @param int $fileId
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(int $fileId): Response
    {
        try {
            $this->deleteFileService->handleDelete($fileId);

            return response([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        } catch (\Exception $error) {
            LogService::error('Error uploading student document.', [
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
