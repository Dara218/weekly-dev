<?php

namespace App\Enum;

/**
 * Stripe Invoice.status values used by billing services.
 */
enum StripeInvoiceStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case PAID = 'paid';
    case VOID = 'void';
    case UNCOLLECTIBLE = 'uncollectible';
}
