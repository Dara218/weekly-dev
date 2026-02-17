<?php

namespace App\Enum;

/**
 * Enums for fee type frequency(fee_types.frequency).
 */
enum FeeTypeFrequency: string
{
    case MONTHLY = 'MONTHLY';
    case TERM = 'TERM';
    case YEARLY = 'YEARLY';

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
