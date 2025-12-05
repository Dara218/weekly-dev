<?php

namespace App\Enum;

/**
 * Enums for invoice status (invoices.status).
 */
enum InvoiceStatus: string
{
    case UNPAID = 'Unpaid';
    case PAID = 'Paid';
    case PARTIAL = 'Partial';

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
