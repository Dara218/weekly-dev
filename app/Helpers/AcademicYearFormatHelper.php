<?php

namespace App\Helpers;

class AcademicYearFormatHelper
{
    /**
     * Get the current academic year in "YYYY-YYYY" format.
     *
     * @return string
     */
    public static function academicYearFormatter()
    {
        $now = now();
        $startYear = $now->month >= 8
            ? $now->year
            : $now->year - 1;

        return $startYear . '-' . ($startYear + 1);
    }

    /**
     * Get the previous academic year in "YYYY-YYYY" format.
     *
     * @return string
     */
    public static function previousAcademicYearFormatter()
    {
        $now = now();
        $startYear = $now->month >= 8
            ? $now->year - 1  // Changed: subtract 1 here
            : $now->year - 2; // Changed: subtract 2 here

        return $startYear . '-' . ($startYear + 1);
    }
}
