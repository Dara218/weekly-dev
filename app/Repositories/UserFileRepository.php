<?php

namespace App\Repositories;

use App\Interfaces\UserFileInterface;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Collection;

class UserFileRepository extends BaseRepository implements UserFileInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\UserFile $model
     */
    public function __construct(UserFile $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByUserId(
        int $userId,
        int $limit = 3,
        int $offset = 0,
    ): Collection {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->skip($offset) // skips the first N records (e.g. offset=3 skips the first 3, so we get the next page)
            ->take($limit) // limits how many records to return (e.g. only 3 at a time)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function isFileExists(string $filePath, int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('file_path', 'LIKE', $filePath)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function countByUserId(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->count();
    }
}
