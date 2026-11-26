<?php

namespace Tests\Unit\Services\Payment;

use App\Interfaces\PaymentInterface;
use App\Services\Payment\{
    StripeCheckoutGateway,
    SyncStripeInvoiceService,
};
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use Stripe\Exception\InvalidRequestException;
use Stripe\Invoice as StripeInvoice;
use Tests\TestCase;

/**
 * Unit tests for Stripe invoice sync helpers and error handling in SyncStripeInvoiceService.
 */
class SyncStripeInvoiceServiceTest extends TestCase
{
    /**
     * Subscription ID is read from parent subscription_details on the Stripe invoice.
     */
    public function testItReadsSubscriptionIdFromParentSubscriptionDetails(): void
    {
        $invoice = StripeInvoice::constructFrom([
            'id' => 'in_test',
            'parent' => [
                'type' => 'subscription_details',
                'subscription_details' => [
                    'subscription' => 'sub_from_parent',
                    'metadata' => [
                        'student_id' => '7',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            'sub_from_parent',
            $this->callPrivate($this->makeService(), 'resolveSubscriptionId', $invoice),
        );
    }

    /**
     * Legacy top-level subscription field is used when parent details are absent.
     */
    public function testItFallsBackToLegacyInvoiceSubscriptionField(): void
    {
        $invoice = StripeInvoice::constructFrom([
            'id' => 'in_legacy',
            'subscription' => 'sub_legacy',
        ]);

        self::assertSame(
            'sub_legacy',
            $this->callPrivate($this->makeService(), 'resolveSubscriptionId', $invoice),
        );
    }

    /**
     * Stripe invoice statuses map to the expected local invoice status values.
     */
    public function testItMapsVoidAndUncollectibleInvoicesToTheirOwnStatuses(): void
    {
        $service = $this->makeService();

        self::assertSame('PAID', $this->callPrivate($service, 'mapStatus', 'paid'));
        self::assertSame('UNPAID', $this->callPrivate($service, 'mapStatus', 'open'));
        self::assertSame('UNPAID', $this->callPrivate($service, 'mapStatus', 'draft'));
        self::assertSame('VOID', $this->callPrivate($service, 'mapStatus', 'void'));
        self::assertSame('UNCOLLECTIBLE', $this->callPrivate($service, 'mapStatus', 'uncollectible'));
    }

    /**
     * student_id on monthly invoice metadata resolves the linked student.
     */
    public function testItResolvesStudentIdFromTheMonthlyInvoiceMetadata(): void
    {
        $invoice = StripeInvoice::constructFrom([
            'id' => 'in_monthly',
            'metadata' => [
                'student_id' => '7',
                'billing_period' => '2026-12',
            ],
        ]);

        self::assertSame(
            7,
            $this->callPrivate($this->makeService(), 'resolveStudentId', $invoice),
        );
    }

    /**
     * student_id on parent subscription metadata resolves the linked student.
     */
    public function testItResolvesStudentIdFromParentSubscriptionMetadata(): void
    {
        $invoice = StripeInvoice::constructFrom([
            'id' => 'in_meta',
            'parent' => [
                'type' => 'subscription_details',
                'subscription_details' => [
                    'subscription' => 'sub_meta',
                    'metadata' => [
                        'student_id' => '42',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            42,
            $this->callPrivate($this->makeService(), 'resolveStudentId', $invoice),
        );
    }

    /**
     * Invoices with no subscription are ignored without calling Stripe.
     */
    public function testItIgnoresAnInvoiceWithNoSubscription(): void
    {
        $invoice = StripeInvoice::constructFrom([
            'id' => 'in_manual',
            'status' => 'open',
        ]);

        $gateway = $this->createMock(StripeCheckoutGateway::class);
        $gateway->expects(self::never())->method('retrieveSubscription');

        // A manual Dashboard invoice is not tuition: no student, no error, nothing to mirror.
        $this->makeService($gateway)->sync($invoice);
    }

    /**
     * A deleted Stripe invoice is swallowed so the webhook can still succeed.
     */
    public function testItIgnoresAnInvoiceThatNoLongerExistsOnStripe(): void
    {
        $gateway = $this->createMock(StripeCheckoutGateway::class);
        $gateway->expects(self::once())
            ->method('retrieveInvoice')
            ->with('in_deleted')
            ->willThrowException(InvalidRequestException::factory('No such invoice', 404));

        $this->makeService($gateway)->syncById('in_deleted');
    }

    /**
     * Non-404 Stripe errors propagate so Stripe will retry the webhook.
     */
    public function testItRethrowsOtherStripeErrorsSoTheWebhookIsRetried(): void
    {
        $gateway = $this->createMock(StripeCheckoutGateway::class);
        $gateway->method('retrieveInvoice')
            ->willThrowException(InvalidRequestException::factory('Rate limited', 429));

        $this->expectException(InvalidRequestException::class);

        $this->makeService($gateway)->syncById('in_any');
    }

    /**
     * Build the service with a mocked gateway.
     *
     * @param \PHPUnit\Framework\MockObject\MockObject|null $gateway The gateway double.
     *
     * @return \App\Services\Payment\SyncStripeInvoiceService
     */
    private function makeService(?MockObject $gateway = null): SyncStripeInvoiceService
    {
        /** @var StripeCheckoutGateway $gateway */
        $gateway ??= $this->createMock(StripeCheckoutGateway::class);

        return new SyncStripeInvoiceService(
            $this->createMock(PaymentInterface::class),
            $gateway,
        );
    }

    /**
     * Invoke a private service method.
     *
     * @param \App\Services\Payment\SyncStripeInvoiceService $service The service under test.
     * @param string $method The private method name.
     * @param mixed ...$arguments The method arguments.
     *
     * @return mixed
     */
    private function callPrivate(SyncStripeInvoiceService $service, string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionMethod($service, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($service, ...$arguments);
    }
}