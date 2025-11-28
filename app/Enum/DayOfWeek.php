<?php

namespace App\Enum;

/**
 * Enums for week days (timetables.day_of_week).
 */
enum DayOfWeek: string
{
    case MONDAY = 'Mon';
    case TUESDAY = 'Tues';
    case WEDNESDAY = 'Wed';
    case THURSDAY = 'Thurs';
    case FRIDAY = 'Fri';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, DayOfWeek::cases());
    }
}
