<?php

namespace App\Services\Payment;

use App\Enum\StripeInvoiceStatus;
use App\Exceptions\PaymentGatewayException;
use Stripe\Invoice as StripeInvoice;

/**
 * Stripe invoice create / void / finalize helpers used by overdue consolidation.
 */
class StripeInvoiceOperations
{
    /** Stripe invoice description for a consolidated overdue draft. */
    private const COMBINED_DESCRIPTION = 'Combined overdue tuition';

    /** Stripe collection_method: customer pays via hosted invoice link. */
    private const COLLECTION_METHOD_SEND_INVOICE = 'send_invoice';

    /**
     * Create a draft combined invoice with one line item per overdue original.
     *
     * The draft has no hosted URL until it is finalized, so nobody can pay it while
     * the originals are still open.
     *
     * @param string $customerId The student's Stripe Customer ID.
     * @param list<array{
     *     amount_minor: int,
     *     description: string,
     *     metadata: array<string, string>
     * }> $lines The line items.
     * @param int $dueAt The due date as a Unix timestamp.
     * @param array<string, string> $metadata Metadata including student_id.
     * @param string $idempotencyKey Stable key for this student and original set.
     *
     * @return \Stripe\Invoice The draft invoice.
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function createConsolidatedDraft(
        string $customerId,
        array $lines,
        int $dueAt,
        array $metadata,
        string $idempotencyKey,
    ): StripeInvoice {
        if ($lines === []) {
            throw new PaymentGatewayException('A consolidated invoice needs at least one line item.');
        }

        $stripe = $this->client();
        $invoiceParams = [
            'customer' => $customerId,
            'currency' => config('services.stripe.currency'),
            'collection_method' => self::COLLECTION_METHOD_SEND_INVOICE,
            'due_date' => $dueAt,
            'auto_advance' => false,
            'description' => self::COMBINED_DESCRIPTION,
            'metadata' => $metadata,
        ];

        $invoice = $stripe->invoices->create($invoiceParams, [
            'idempotency_key' => StripeCheckoutGateway::idempotencyKey(
                $idempotencyKey . '-invoice',
                $invoiceParams,
            ),
        ]);

        $existingItems = $stripe->invoiceItems->all([
            'invoice' => $invoice->id,
            'limit' => count($lines),
        ]);

        if (count($existingItems->data) === 0) {
            foreach ($lines as $index => $line) {
                $stripe->invoiceItems->create([
                    'customer' => $customerId,
                    'invoice' => $invoice->id,
                    'amount' => $line['amount_minor'],
                    'currency' => config('services.stripe.currency'),
                    'description' => $line['description'],
                    'metadata' => $line['metadata'],
                ], [
                    'idempotency_key' => StripeCheckoutGateway::idempotencyKey(
                        $idempotencyKey . '-item-' . $index,
                        $line,
                    ),
                ]);
            }
        }

        return $stripe->invoices->retrieve($invoice->id);
    }

    /**
     * Void an open Stripe invoice. Already-void invoices are returned unchanged.
     *
     * @param string $invoiceId The Stripe Invoice ID.
     *
     * @return \Stripe\Invoice
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function voidInvoice(string $invoiceId): StripeInvoice
    {
        $invoice = $this->retrieveInvoice($invoiceId);

        if ($invoice->status === StripeInvoiceStatus::VOID->value) {
            return $invoice;
        }

        if ($invoice->status === StripeInvoiceStatus::PAID->value) {
            throw new PaymentGatewayException(
                'Stripe invoice ' . $invoiceId . ' was paid and cannot be voided.',
            );
        }

        return $this->client()->invoices->voidInvoice($invoiceId);
    }

    /**
     * Delete a draft Stripe invoice. Open invoices must be voided instead.
     *
     * @param string $invoiceId The Stripe Invoice ID.
     *
     * @return void
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function deleteDraftInvoice(string $invoiceId): void
    {
        $invoice = $this->retrieveInvoice($invoiceId);

        if ($invoice->status !== StripeInvoiceStatus::DRAFT->value) {
            throw new PaymentGatewayException(
                'Stripe invoice ' . $invoiceId . ' is ' . $invoice->status . ' and cannot be deleted as a draft.',
            );
        }

        $this->client()->invoices->delete($invoiceId);
    }

    /**
     * Finalize a draft invoice and return the open invoice with a hosted URL.
     *
     * @param string $invoiceId The Stripe Invoice ID.
     *
     * @return \Stripe\Invoice
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function finalizeInvoice(string $invoiceId): StripeInvoice
    {
        $invoice = $this->retrieveInvoice($invoiceId);

        if ($invoice->status === StripeInvoiceStatus::DRAFT->value) {
            $invoice = $this->client()->invoices->finalizeInvoice($invoiceId);
        }

        if (! is_string($invoice->hosted_invoice_url) || $invoice->hosted_invoice_url === '') {
            throw new PaymentGatewayException('Stripe did not return a hosted invoice URL.');
        }

        return $invoice;
    }

    /**
     * Retrieve a Stripe Invoice.
     *
     * @param string $invoiceId The Stripe Invoice ID.
     *
     * @return \Stripe\Invoice
     */
    public function retrieveInvoice(string $invoiceId): StripeInvoice
    {
        return $this->client()->invoices->retrieve($invoiceId);
    }

    /**
     * Build a Stripe API client.
     *
     * @return \Stripe\StripeClient
     */
    private function client(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient(config('services.stripe.secret'));
    }
}
