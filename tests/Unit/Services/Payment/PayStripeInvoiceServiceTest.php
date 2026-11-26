<?php

namespace Tests\Unit\Services\Payment;

use App\Enum\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Payment\PayStripeInvoiceService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for hosted-invoice URL resolution and status guards in PayStripeInvoiceService.
 */
class PayStripeInvoiceServiceTest extends TestCase
{
    /**
     * Unpaid invoices return the Stripe hosted invoice payment URL.
     */
    public function testItReturnsTheHostedUrlForAnUnpaidInvoice(): void
    {
        $invoice = new Invoice();
        $invoice->forceFill([
            'status' => InvoiceStatus::UNPAID->value,
            'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/test',
        ]);

        self::assertSame(
            ['url' => 'https://invoice.stripe.com/i/test'],
            (new PayStripeInvoiceService())->handle($invoice),
        );
    }

    /**
     * Void, uncollectible, and paid invoices cannot enter the pay flow.
     */
    public function testItRejectsVoidAndUncollectibleInvoices(): void
    {
        foreach ([InvoiceStatus::VOID, InvoiceStatus::UNCOLLECTIBLE, InvoiceStatus::PAID] as $status) {
            $invoice = new Invoice();
            $invoice->forceFill([
                'status' => $status->value,
                'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/test',
            ]);

            try {
                (new PayStripeInvoiceService())->handle($invoice);
                self::fail('Expected '.$status->value.' to be rejected.');
            } catch (ValidationException) {
                self::assertTrue(true);
            }
        }
    }
}