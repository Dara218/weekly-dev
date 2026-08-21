<?php

namespace App\Services\User;

use App\Helpers\FormatFilePathHelper;
use App\Interfaces\UserFileInterface;
use App\Services\Common\StorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadFileService
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
     * @var \App\Interfaces\UserFileInterface $userFileInterface
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
     * Upload the file in storage bucket and save the path in the database.
     *
     * @param int $userId
     * @param \Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handleUpload(int $userId, UploadedFile $uploadedFile): Model
    {
        DB::beginTransaction();

        try {
            $file = file_get_contents($uploadedFile->getRealPath());

            if ($file === false) {
                throw new \RuntimeException('Unable to read uploaded file contents.');
            }

            $fileName = $uploadedFile->getClientOriginalName();
            $fileType = $uploadedFile->getMimeType();
            $filePath = FormatFilePathHelper::formatStudentFilePath($userId, $fileName);

            $count = 0;
            while ($this->userFileInterface->isFileExists($filePath, $userId)) {
                $count++;
                $filePath = FormatFilePathHelper::formatStudentFilePath($userId, $fileName, $count);
            }

            // Upload the file in storage
            $this->storageService->put($filePath, $file);

            // Save the path in database
            $data = $this->userFileInterface->create([
                'user_id' => $userId,
                'file_path' => $filePath,
                'file_type' => $fileType,
            ]);

            DB::commit();

            return $data;
        } catch (\Exception $error) {
            DB::rollBack();

            throw $error;
        }
    }
}
