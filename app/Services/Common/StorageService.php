<?php

namespace App\Services\Common;

use Illuminate\Http\{
    File,
    UploadedFile,
};
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\StreamInterface;

class StorageService
{
    /**
     * Filesystem instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    /**
     * Channel for logging
     *
     * @var string
     */
    public const CHANNEL = 's3';

    /**
     * Setup the service.
     */
    public function __construct()
    {
        $this->storage = Storage::disk(self::CHANNEL);
    }

    /**
     * Saves the file contents to storage in the specified directory and filename.
     *
     * @param string $path The file path of the file
     * @param \Psr\Http\Message\StreamInterface
     *  |\Illuminate\Http\File
     *  |\Illuminate\Http\UploadedFile
     *  |string $contents
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function put(
        string $path,
        StreamInterface|File|UploadedFile|string $contents,
        array $options = [],
    ): bool {
        try {
            return $this->storage->put(
                $path,
                $contents,
                $options
            );
        } catch (\Throwable $error) {
            LogService::error('Error saving the file.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            throw $error;
        }
    }

    /**
     * Checks if a file exists given a filepath.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        try {
            return $this->storage->exists($path);
        } catch (\Throwable $error) {
            LogService::error('Error checking the file.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            throw $error;
        }
    }

    /**
     * Get the file from the storage.
     *
     * @param string $path
     *
     * @return string
     */
    public function get(string $path): string
    {
        try {
            $contents = $this->storage->get($path);

            if ($contents === null) {
                throw new \RuntimeException("File contents for path [{$path}] is null.");
            }

            return $contents;
        } catch (\Throwable $error) {
            LogService::error('Error fetching the file.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            throw $error;
        }
    }

    /**
     * Deletes the file in the storage.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete(string $path): bool
    {
        try {
            return $this->storage->delete($path);
        } catch (\Throwable $error) {
            LogService::error('Error deleting the file.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            throw $error;
        }
    }
}
