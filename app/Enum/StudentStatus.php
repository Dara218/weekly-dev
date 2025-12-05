<?php

namespace App\Enum;

/**
 * Enum for student status (students.status).
 */
enum StudentStatus: string
{
    case ACTIVE = 'Active';
    case ALUMNI = 'Alumni';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, StudentStatus::cases());
    }
}
