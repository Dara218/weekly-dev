<?php

namespace App\Interfaces;

use App\Models\Payment;

interface PaymentInterface
{
    /**
     * Store a payment.
     *
     * @param array<string, mixed> $data The payment attributes.
     *
     * @return \App\Models\Payment
     */

    public function create(array $data): Payment;
    /**
     * Find a payment by its Stripe Invoice ID.
     *
     * @param string $invoiceId The Stripe Invoice ID.
     *
     * @return \App\Models\Payment|null
     */
    public function findByStripeInvoiceId(string $invoiceId): ?Payment;
}
