<?php

namespace App\Enum;

/**
 * Enums for teacher class assignment status (teacher_class_assignments.status).
 */
enum TeacherClassAssignmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case PENDING = 'PENDING';
    case ARCHIVED = 'ARCHIVED';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, TeacherClassAssignmentStatus::cases());
    }
}
