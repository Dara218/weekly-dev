<?php

namespace App\Services\User;

use App\Interfaces\UserFileInterface;
use App\Models\UserFile;
use App\Services\Common\StorageService;

class GetFileService
{
    /**
     * Initialize StorageService.
     *
     * @var \App\Services\Common\StorageService $storageService
     */
    protected StorageService $storageService;

    /**
     * Initialize UserFileInterface.
     *
     * @var \App\Interfaces\UserFileInterface
     */
    protected UserFileInterface $userFileInterface;

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
     * Fetch the file in storage bucket and save the path in the database.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function handleGetDocument(
        int $userId,
        int $limit,
        int $offset,
    ): mixed {
        try {
            /** @var \Illuminate\Support\Collection<int, \App\Models\UserFile> $userDocuments */
            $userDocuments = $this->userFileInterface->findByUserId(
                $userId,
                $limit,
                $offset,
            );

            if ($userDocuments->isEmpty()) {
                return null;
            }

            $uploadedDocuments = [];
            foreach ($userDocuments as $userDocument) {
                $filePath = $userDocument->file_path;
                $isExists = $this->storageService->exists($filePath);

                if (!$isExists) {
                    continue;
                }

                $uploadedDocuments[] = $userDocument;
            }

            return $uploadedDocuments;
        } catch (\Exception $error) {
            throw $error;
        }
    }
}
