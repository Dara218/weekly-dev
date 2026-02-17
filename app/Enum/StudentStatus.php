<?php

namespace App\Enum;

/**
 * Enum for student status (students.status).
 */
enum StudentStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, int>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, StudentStatus::cases());
    }
}
