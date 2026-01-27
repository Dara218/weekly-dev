<?php

namespace App\Interfaces;

interface StudentInterface extends BaseInterface
{
    /**
     * Get students filtered by the given search keywords.
     *
     * @param array<mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentsBySearch(array $keywords);
}
