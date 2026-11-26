<?php

namespace App\Services\Payment;

use App\Enum\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Validation\ValidationException;

class PayStripeInvoiceService
{
    /**
     * Return the Stripe hosted invoice URL for an unpaid mirrored invoice.
     *
     * @param \App\Models\Invoice $invoice The local invoice mirror.
     *
     * @return array{url: string}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Invoice $invoice): array
    {
        if ($invoice->status !== InvoiceStatus::UNPAID->value) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice is not payable.',
            ]);
        }

        if (
            !is_string($invoice->stripe_hosted_invoice_url)
            || $invoice->stripe_hosted_invoice_url === ''
        ) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice has no Stripe payment page yet. Wait for the webhook or refresh Fees.',
            ]);
        }

        return ['url' => $invoice->stripe_hosted_invoice_url];
    }
}
