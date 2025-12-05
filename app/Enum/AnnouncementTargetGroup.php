<?php

namespace App\Enum;

/**
 * Enums for announcement target group (announcements.target_group).
 */
enum AnnouncementTargetGroup: string
{
    case ALL = 'All';
    case TEACHERS = 'Teachers';
    case STUDENTS = 'Students';
    case PARENTS = 'Parents';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, AnnouncementTargetGroup::cases());
    }
}
