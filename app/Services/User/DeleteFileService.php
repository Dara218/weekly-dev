<?php

namespace App\Services\User;

use App\Interfaces\UserFileInterface;
use App\Services\Common\StorageService;
use Illuminate\Support\Facades\DB;

class DeleteFileService
{
    /**
     * Repository interface for user file.
     *
     * @var \App\Interfaces\UserFileInterface
     */
    protected UserFileInterface $userFileInterface;

    /**
     * Initialize StorageService.
     *
     * @var \App\Services\Common\StorageService $storageService
     */
    protected StorageService $storageService;

    /**
     * Initialize the service.
     *
     * @param \App\Services\Common\StorageService $storageService
     * @param \App\Interfaces\UserFileInterface $userFileInterface
     */
    public function __construct(StorageService $storageService, UserFileInterface $userFileInterface)
    {
        $this->storageService = $storageService;
        $this->userFileInterface = $userFileInterface;
    }

    /**
     * Upload the file in storage bucket and save the path in the database.
     *
     * @param int $fileId
     *
     * @return void
     */
    public function handleDelete(int $fileId): void
    {
        DB::beginTransaction();

        try {
            $document = $this->userFileInterface->find($fileId);
            $filePath = $document->file_path;

            // Delete the file in the storage
            $this->storageService->delete($filePath);

            // Soft delete the path in database
            $this->userFileInterface->delete($fileId);

            DB::commit();
        } catch (\Exception $error) {
            DB::rollBack();

            throw $error;
        }
    }
}
