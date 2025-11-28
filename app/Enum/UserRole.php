<?php

namespace App\Enum;

/**
 * Enums for user roles (users.role).
 */
enum UserRole: string
{
    case ADMIN = 'Admin';
    case TEACHER = 'Teacher';
    case STUDENT = 'Student';
    case PARENT = 'Parent';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list(): array
    {
        return array_map(fn(self $enum) => $enum->value, UserRole::cases());
    }
}
