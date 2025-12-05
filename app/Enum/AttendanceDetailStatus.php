<?php

namespace App\Enum;

/**
 * Enums for attendance detail status (attendance_details.status).
 */
enum AttendanceDetailStatus: string
{
    case PRESENT = 'Present';
    case ABSENT = 'Absent';
    case LATE = 'Late';
    case EXCUSED = 'Excuse';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, AttendanceDetailStatus::cases());
    }
}
