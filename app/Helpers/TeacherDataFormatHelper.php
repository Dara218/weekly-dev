<?php

namespace App\Helpers;

class TeacherDataFormatHelper
{
    /**
     * Format a teacher employee code.
     *
     * @param int $sequenceNumber
     *
     * @return string
     */
    public static function employeeCodeFormatter(int $sequenceNumber)
    {
        return 'TCH' . '-' . now()->year . '-' . $sequenceNumber;
    }
}
