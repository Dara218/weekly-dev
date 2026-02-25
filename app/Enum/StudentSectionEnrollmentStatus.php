<?php

namespace App\Enum;

/**
 * Enum for student section enrollments (student_section_enrollments.status).
 */
enum StudentSectionEnrollmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case TRANSFERRED = 'TRANSFERRED';
    case PENDING = 'PENDING';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, StudentSectionEnrollmentStatus::cases());
    }
}
