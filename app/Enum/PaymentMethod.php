<?php

namespace App\Enum;

/**
 * Enums for payment method (payments.method).
 */
enum PaymentMethod: string
{
    case CASH = 'CASH';
    case BANK = 'BANK';
    case ONLINE = 'ONLINE';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, PaymentMethod::cases());
    }
}
