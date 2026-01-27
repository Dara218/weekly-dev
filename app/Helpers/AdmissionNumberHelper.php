<?php

namespace App\Helpers;

class AdmissionNumberHelper
{
    /**
     * Format the admission number with the current academic start year and sequence.
     *
     * @param int $sequenceNumber
     *
     * @return string
     */
    public static function formatAdmissionNumber(int $sequenceNumber)
    {
        $now = now();
        $startYear = $now->month >= 8
            ? $now->year
            : $now->year - 1;

        return $startYear . str_pad((string) $sequenceNumber, 5, '0', STR_PAD_LEFT);
    }
}
