<?php

namespace App\Enum;

/**
 * Enums for fee type frequency(fee_types.frequency).
 */
enum FeeTypeFrequency: string
{
    case MONTHLY = 'Monthly';
    case TERM = 'Term';
    case YEARLY = 'Yearly';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, FeeTypeFrequency::cases());
    }
}
