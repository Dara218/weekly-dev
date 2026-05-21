<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TeacherInterface extends BaseInterface
{
    /**
     * Get teachers filtered by the given search keywords.
     *
     * @param array<mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeachersBySearch(array $keywords): Collection;
}
