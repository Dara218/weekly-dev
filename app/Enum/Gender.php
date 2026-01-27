<?php

namespace App\Enum;

/**
 * Enums for student gender (students.gender).
 */
enum Gender: string
{
    case MALE = 'MALE';
    case FEMALE = 'FEMALE';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, Gender::cases());
    }
}
