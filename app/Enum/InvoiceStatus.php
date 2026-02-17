<?php

namespace App\Enum;

/**
 * Enums for invoice status (invoices.status).
 */
enum InvoiceStatus: string
{
    case UNPAID = 'UNPAID';
    case PAID = 'PAID';
    case PARTIAL = 'PARTIAL';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, InvoiceStatus::cases());
    }
}
