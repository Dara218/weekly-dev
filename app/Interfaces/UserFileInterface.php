<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface UserFileInterface extends BaseInterface
{
    /**
     * Get the user file with limit and offset. Use for "Load more" modal to load the file by 3.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUserId(
        int $userId,
        int $limit = 3,
        int $offset = 0,
    ): Collection;

    /**
     * Check if the user file exist by checking the file path.
     *
     * @param string $filePath
     * @param int $userId
     *
     * @return bool
     */
    public function isFileExists(string $filePath, int $userId): bool;

    /**
     * Count the number of file of the user.
     *
     * @param int $userId
     *
     * @return int
     */
    public function countByUserId(int $userId): int;
}
