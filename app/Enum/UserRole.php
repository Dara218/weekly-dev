<?php

namespace App\Enum;

/**
 * Enums for user roles (users.role).
 */
enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case TEACHER = 'TEACHER';
    case STUDENT = 'STUDENT';
    case PARENT = 'PARENT';

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
