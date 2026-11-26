<?php

namespace Tests\Feature\Payment;

use App\Interfaces\InvoiceInterface;
use App\Interfaces\PaymentInterface;
use App\Repositories\{
    InvoiceRepository,
    PaymentRepository,
};
use Illuminate\Routing\Route;
use Tests\TestCase;

/**
 * Route wiring checks for invoice, pay-overdue, parent children, and Stripe webhook endpoints.
 */
class StripeRoutesTest extends TestCase
{
    /**
     * Invoice and parent routes require Sanctum; the webhook stays public and bindings resolve.
     */
    public function testInvoiceAndWebhookRoutesHaveTheExpectedAuthenticationBoundary(): void
    {
        $routes = app('router')->getRoutes();

        $index = $routes->getByName('invoices.index');
        $pay = $routes->getByName('invoices.pay');
        $payOverdue = $routes->getByName('invoices.pay-overdue');
        $children = $routes->getByName('parent.children');
        $webhook = $routes->getByName('stripe.webhook');

        self::assertInstanceOf(Route::class, $index);
        self::assertInstanceOf(Route::class, $pay);
        self::assertInstanceOf(Route::class, $payOverdue);
        self::assertInstanceOf(Route::class, $children);
        self::assertInstanceOf(Route::class, $webhook);
        self::assertContains('auth:sanctum', $index->gatherMiddleware());
        self::assertContains('auth:sanctum', $pay->gatherMiddleware());
        self::assertContains('auth:sanctum', $payOverdue->gatherMiddleware());
        self::assertContains('auth:sanctum', $children->gatherMiddleware());
        self::assertSame('api/parent/children', $children->uri());
        self::assertSame('api/invoices/pay-overdue', $payOverdue->uri());
        self::assertNotContains('auth:sanctum', $webhook->gatherMiddleware());
        self::assertInstanceOf(InvoiceRepository::class, app(InvoiceInterface::class));
        self::assertInstanceOf(PaymentRepository::class, app(PaymentInterface::class));
    }

    /**
     * Legacy tuition subscription route names are no longer registered.
     */
    public function testTheSubscriptionRoutesAreGone(): void
    {
        $routes = app('router')->getRoutes();

        self::assertNull($routes->getByName('tuition.status'));
        self::assertNull($routes->getByName('tuition.subscribe'));
    }
}
