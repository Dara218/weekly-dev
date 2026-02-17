<?php

namespace App\Services\Common;

use App\Interfaces\AcademicYearInterface;
use App\Repositories\StudentRepository;

class AdmissionNumberService
{
    /**
     * Generate admission number based on academic year and student count.
     *
     * @param int $academicYearId The academic year ID
     *
     * @return string Formatted admission number (e.g., 202500001)
     */
    public static function generate(int $academicYearId): string
    {
        // Get the academic year
        $academicYear = app(AcademicYearInterface::class)->find($academicYearId);

        // Extract start year from academic year name (e.g., "2025-2026" -> 2025)
        $startYear = self::extractStartYear($academicYear->name);

        // Get the count of students in this academic year + 1 for the new student
        $studentCount = app(StudentRepository::class)->getStudentCountByCurrentYear($academicYearId);

        // Format: YYYY + 5-digit sequence number
        return $startYear . str_pad((string) $studentCount, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Extract the start year from academic year name.
     *
     * @param string $academicYearName (e.g., "2025-2026")
     *
     * @return string The start year (e.g., "2025")
     */
    protected static function extractStartYear(string $academicYearName): string
    {
        // Split by dash and get the first year
        $years = explode('-', $academicYearName);

        return $years[0] ?? date('Y');
    }
}
