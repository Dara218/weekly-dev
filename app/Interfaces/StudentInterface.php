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

    /**
     * Get the total student count for the given academic year.
     *
     * @param int $academicYearId
     *
     * @return int
     */
    public function getStudentCountByCurrentYear(int $academicYearId);
}
