<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUpload\StudentFileUploadRequest;
use App\Services\Common\LogService;
use App\Services\User\UploadFileService;
use Illuminate\Http\Response;

class UploadStudentFileController extends Controller
{
    /**
     * Initialize UploadFileService.
     *
     * @var \App\Services\User\UploadFileService $uploadFileService
     */
    protected UploadFileService $uploadFileService;

    /**
     * Initialize the controller.
     *
     * @param \App\Services\User\UploadFileService $uploadFileService
     */
    public function __construct(UploadFileService $uploadFileService)
    {
        $this->uploadFileService = $uploadFileService;
    }

    /**
     * Upload the file in the storage.
     *
     * @param \App\Http\Requests\FileUpload\StudentFileUploadRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(StudentFileUploadRequest $request): Response
    {
        $file = $request->validated('file');
        $studentId = $request->validated('id');

        try {
            $data = $this->uploadFileService->handleUpload($studentId, $file);

            return response([
                'success' => true,
                'message' => 'File uploaded successfully.',
                'data' => $data,
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
