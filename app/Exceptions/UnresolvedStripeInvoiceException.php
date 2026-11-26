<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A tuition invoice could not be linked to a local student yet.
 *
 * Throwing this makes the webhook return a non-2xx response so Stripe retries.
 */
class UnresolvedStripeInvoiceException extends RuntimeException
{
}
