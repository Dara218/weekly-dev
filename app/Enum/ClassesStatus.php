<?php

namespace App\Enum;

/**
 * Enums for classes status (classes.status).
 */
enum ClassesStatus: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case ARCHIVED = 'ARCHIVED';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, ClassesStatus::cases());
    }
}
