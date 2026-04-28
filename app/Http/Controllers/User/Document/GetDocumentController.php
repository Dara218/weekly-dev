<?php

namespace App\Http\Controllers\User\Document;

use App\Http\Controllers\Controller;
use App\Interfaces\UserFileInterface;
use App\Services\Common\LogService;
use App\Services\User\GetFileService;
use Illuminate\Http\{
    Request,
    Response,
};

class GetDocumentController extends Controller
{
    /**
     * Initialize GetFileService instance.
     *
     * @var \App\Services\User\GetFileService $getFileService
     */
    protected GetFileService $getFileService;

    /**
     * Initialize classes.
     *
     * @param \App\Services\User\GetFileService $getFileService
     */
    public function __construct(GetFileService $getFileService)
    {
        $this->getFileService = $getFileService;
    }

    /**
     * Get the document of the user.
     *
     * @param Request $request
     * @param int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request, int $userId): Response
    {
        $limit = $request->input('limit', config('constant.load_items_limit'));
        $offset = $request->input('offset', config('constant.load_items_offset'));

        try {
            $documents = $this->getFileService->handleGetDocument(
                $userId,
                $limit,
                $offset,
            );

            $total = app(UserFileInterface::class)->countByUserId($userId);

            return response([
                'success' => true,
                'data' => $documents,
                'hasMore' => ($offset + $limit) < $total,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            LogService::error('User has no existing file.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            return response([
                'success' => false,
                'message' => 'User has no existing file.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $error) {
            LogService::error('Error fetching user documents.', [
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
