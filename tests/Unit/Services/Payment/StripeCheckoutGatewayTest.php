<?php

namespace Tests\Unit\Services\Payment;

use App\Services\Payment\StripeCheckoutGateway;
use Tests\TestCase;

/**
 * Unit tests for StripeCheckoutGateway currency conversion and idempotency helpers.
 */
class StripeCheckoutGatewayTest extends TestCase
{
    /**
     * Two-decimal currencies divide Stripe minor units into decimal amounts.
     */
    public function testItConvertsTwoDecimalMinorUnitsToADecimalAmount(): void
    {
        config(['services.stripe.currency' => 'php']);

        self::assertSame('10000.00', (new StripeCheckoutGateway())->fromMinorUnits(1000000));
        self::assertSame('0.50', (new StripeCheckoutGateway())->fromMinorUnits(50));
    }

    /**
     * Zero-decimal currencies treat minor units as whole currency units.
     */
    public function testItTreatsZeroDecimalCurrenciesAsWholeUnits(): void
    {
        config(['services.stripe.currency' => 'jpy']);

        self::assertSame('10000.00', (new StripeCheckoutGateway())->fromMinorUnits(10000));
        self::assertTrue(StripeCheckoutGateway::usesZeroDecimalMinorUnits('JPY'));
        self::assertFalse(StripeCheckoutGateway::usesZeroDecimalMinorUnits('php'));
    }

    /**
     * Decimal amounts round-trip to Stripe minor units for two-decimal currencies.
     */
    public function testItConvertsDecimalAmountsToMinorUnits(): void
    {
        config(['services.stripe.currency' => 'php']);

        self::assertSame(1000000, (new StripeCheckoutGateway())->toMinorUnits('10000.00'));
        self::assertSame(50, (new StripeCheckoutGateway())->toMinorUnits('0.50'));
    }

    /**
     * Identical scope and parameters yield the same idempotency key.
     */
    public function testIdempotencyKeyIsStableForIdenticalParameters(): void
    {
        $params = ['name' => 'Ana Student', 'metadata' => ['student_id' => '1']];

        self::assertSame(
            StripeCheckoutGateway::idempotencyKey('customer-student-1', $params),
            StripeCheckoutGateway::idempotencyKey('customer-student-1', $params),
        );
    }

    /**
     * Changing parameters produces a different idempotency key under the same scope.
     */
    public function testIdempotencyKeyChangesWhenParametersChange(): void
    {
        $original = StripeCheckoutGateway::idempotencyKey('customer-student-1', ['name' => 'Ana Student']);
        $renamed = StripeCheckoutGateway::idempotencyKey('customer-student-1', ['name' => 'Ana Reyes']);

        self::assertNotSame($original, $renamed);
        self::assertStringStartsWith('customer-student-1-', $original);
        self::assertStringStartsWith('customer-student-1-', $renamed);
    }
}