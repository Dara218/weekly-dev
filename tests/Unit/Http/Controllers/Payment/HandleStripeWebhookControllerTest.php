<?php

namespace Tests\Unit\Http\Controllers\Payment;

use App\Http\Controllers\Payment\HandleStripeWebhookController;
use App\Services\Payment\{
    StripeCheckoutGateway,
    SyncStripeInvoiceService,
};
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Event;
use Tests\TestCase;

/**
 * Unit tests for Stripe webhook signature checks and invoice event dispatch.
 */
class HandleStripeWebhookControllerTest extends TestCase
{
    /**
     * Requests missing the Stripe-Signature header are rejected with HTTP 400.
     */
    public function testItRejectsARequestWithoutASignature(): void
    {
        $response = $this->makeController(
            $this->createMock(StripeCheckoutGateway::class),
            $this->createMock(SyncStripeInvoiceService::class),
        )($this->makeRequest(withSignature: false));

        self::assertSame(400, $response->getStatusCode());
    }

    /**
     * Invoice lifecycle events trigger a sync from the live Stripe invoice.
     */
    public function testInvoiceEventsAreMirroredFromTheLiveStripeInvoice(): void
    {
        foreach (['invoice.created', 'invoice.finalized', 'invoice.updated', 'invoice.paid', 'invoice.payment_succeeded'] as $type) {
            $invoices = $this->createMock(SyncStripeInvoiceService::class);
            $invoices->expects(self::once())->method('syncById')->with('in_123');

            $response = $this->makeController(
                $this->gatewayReturning($type, ['id' => 'in_123']),
                $invoices,
            )($this->makeRequest());

            self::assertSame(200, $response->getStatusCode(), $type);
        }
    }

    /**
     * Legacy subscription and checkout events are acknowledged without syncing invoices.
     */
    public function testSubscriptionAndCheckoutEventsAreNoLongerProcessed(): void
    {
        foreach ([
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
            'checkout.session.completed',
        ] as $type) {
            $invoices = $this->createMock(SyncStripeInvoiceService::class);
            $invoices->expects(self::never())->method('syncById');

            $response = $this->makeController(
                $this->gatewayReturning($type, ['id' => 'obj_1']),
                $invoices,
            )($this->makeRequest());

            self::assertSame(200, $response->getStatusCode(), $type);
        }
    }

    /**
     * Unhandled event types return HTTP 200 without calling the invoice sync service.
     */
    public function testUnrelatedEventsAreAcknowledgedWithoutAnyWork(): void
    {
        $invoices = $this->createMock(SyncStripeInvoiceService::class);
        $invoices->expects(self::never())->method('syncById');

        $response = $this->makeController(
            $this->gatewayReturning('charge.succeeded', ['id' => 'ch_1']),
            $invoices,
        )($this->makeRequest());

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * Handler failures return HTTP 500 so Stripe retries delivery.
     */
    public function testAFailureReturns500SoStripeRetriesTheEvent(): void
    {
        $invoices = $this->createMock(SyncStripeInvoiceService::class);
        $invoices->method('syncById')->willThrowException(new \RuntimeException('Database is down.'));

        $response = $this->makeController(
            $this->gatewayReturning('invoice.paid', ['id' => 'in_123']),
            $invoices,
        )($this->makeRequest());

        self::assertSame(500, $response->getStatusCode());
    }

    /**
     * Build a gateway double that returns a verified event.
     *
     * @param string $type The Stripe event type.
     * @param array<string, mixed> $object The event data object.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function gatewayReturning(string $type, array $object): MockObject
    {
        $gateway = $this->createMock(StripeCheckoutGateway::class);
        $gateway->method('constructWebhookEvent')->willReturn(Event::constructFrom([
            'id' => 'evt_test',
            'type' => $type,
            'data' => ['object' => $object],
        ]));

        return $gateway;
    }

    /**
     * Build the controller from doubles.
     *
     * @param \PHPUnit\Framework\MockObject\MockObject $gateway The gateway double.
     * @param \PHPUnit\Framework\MockObject\MockObject $invoices The invoice service double.
     *
     * @return \App\Http\Controllers\Payment\HandleStripeWebhookController
     */
    private function makeController(
        MockObject $gateway,
        MockObject $invoices,
    ): HandleStripeWebhookController {
        /** @var StripeCheckoutGateway $gateway */
        /** @var SyncStripeInvoiceService $invoices */
        return new HandleStripeWebhookController($gateway, $invoices);
    }

    /**
     * Build a webhook request.
     *
     * @param bool $withSignature Whether to send a Stripe-Signature header.
     *
     * @return \Illuminate\Http\Request
     */
    private function makeRequest(bool $withSignature = true): Request
    {
        $request = Request::create('/api/stripe/webhook', 'POST', [], [], [], [], '{}');

        if ($withSignature) {
            $request->headers->set('Stripe-Signature', 't=1,v1=test');
        }

        return $request;
    }
}