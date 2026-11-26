<?php

namespace App\Services\Payment;

use App\Enum\StripeInvoiceStatus;
use App\Exceptions\PaymentGatewayException;
use App\Models\{
    Student,
    User,
};
use Stripe\Invoice as StripeInvoice;
use Stripe\Price;
use Stripe\Subscription;

class StripeCheckoutGateway
{
    /**
     * ISO currencies Stripe treats as zero-decimal (amount is already in major units).
     *
     * @var list<string>
     */
    private const ZERO_DECIMAL_CURRENCIES = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
        'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    /** Stripe collection_method: customer pays via hosted invoice link. */
    private const COLLECTION_METHOD_SEND_INVOICE = 'send_invoice';

    /** Characters of the params hash appended to Stripe idempotency keys. */
    private const IDEMPOTENCY_HASH_LENGTH = 12;

    /** Cached tuition Price ID for this gateway instance (one generate/request). */
    private ?string $cachedTuitionPriceId = null;

    /** Cached tuition amount in minor units for this gateway instance. */
    private ?int $cachedTuitionAmount = null;

    /**
     * Build a Stripe idempotency key from a stable base and the request parameters.
     *
     * Stripe rejects a key reused with different parameters for 24 hours. Adding a short hash of
     * the parameters keeps an identical retry safe (same key, replayed) while a changed request
     * (edited name or email, new currency) gets a fresh key instead of an error.
     *
     * @param string $base Stable key for the operation, such as customer-student-5.
     * @param array<string, mixed> $params The Stripe request parameters.
     *
     * @return string
     */
    public static function idempotencyKey(string $base, array $params): string
    {
        return $base . '-' . substr(
            hash('sha256', json_encode($params, JSON_THROW_ON_ERROR)),
            0,
            self::IDEMPOTENCY_HASH_LENGTH,
        );
    }

    /**
     * Create or reuse the Stripe Customer that owns a student's monthly invoices.
     *
     * @param \App\Models\Student $student The student profile.
     *
     * @return string The Stripe Customer ID.
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function ensureStudentCustomer(Student $student): string
    {
        if (is_string($student->stripe_customer_id) && $student->stripe_customer_id !== '') {
            return $student->stripe_customer_id;
        }

        /** @var User|null $user */
        $user = $student->user;

        $params = [
            'name' => $user === null
                ? 'Student ' . $student->id
                : trim(implode(' ', array_filter([
                    $user->first_name,
                    $user->middle_name,
                    $user->last_name,
                ]))),
            'metadata' => [
                'student_id' => (string) $student->id,
            ],
        ];

        if ($user !== null && is_string($user->email) && $user->email !== '') {
            $params['email'] = $user->email;
        }

        $customer = $this->client()->customers->create($params, [
            'idempotency_key' => self::idempotencyKey('customer-student-' . $student->id, $params),
        ]);

        if (! is_string($customer->id) || $customer->id === '') {
            throw new PaymentGatewayException('Stripe did not return a Customer ID.');
        }

        $student->forceFill(['stripe_customer_id' => $customer->id])->save();

        return $customer->id;
    }

    /**
     * Resolve the tuition Price ID from config or the Product.
     *
     * @return string The Stripe Price ID.
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function resolveTuitionPriceId(): string
    {
        if (is_string($this->cachedTuitionPriceId) && $this->cachedTuitionPriceId !== '') {
            return $this->cachedTuitionPriceId;
        }

        $configuredPriceId = config('services.stripe.price_id');

        if (is_string($configuredPriceId) && str_starts_with($configuredPriceId, 'price_')) {
            return $this->cachedTuitionPriceId = $configuredPriceId;
        }

        $productId = config('services.stripe.product_id');

        if (! is_string($productId) || $productId === '') {
            throw new PaymentGatewayException(
                'Set STRIPE_PRICE_ID or STRIPE_PRODUCT_ID in the environment.',
            );
        }

        $stripe = $this->client();
        $product = $stripe->products->retrieve($productId);
        $defaultPriceId = $product->default_price;

        if (is_string($defaultPriceId) && str_starts_with($defaultPriceId, 'price_')) {
            return $this->cachedTuitionPriceId = $defaultPriceId;
        }

        if ($defaultPriceId instanceof Price && is_string($defaultPriceId->id)) {
            return $this->cachedTuitionPriceId = $defaultPriceId->id;
        }

        $prices = $stripe->prices->all([
            'product' => $productId,
            'active' => true,
            'limit' => 1,
        ]);

        $price = $prices->data[0] ?? null;

        if ($price instanceof Price && is_string($price->id)) {
            return $this->cachedTuitionPriceId = $price->id;
        }

        throw new PaymentGatewayException(
            'No active Stripe Price found for the configured Tuition product.',
        );
    }

    /**
     * Read the fixed monthly tuition amount in Stripe minor units.
     *
     * Cached on this gateway instance so a generate run (or request) only hits Stripe once.
     *
     * @return int
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function resolveTuitionAmount(): int
    {
        if ($this->cachedTuitionAmount !== null) {
            return $this->cachedTuitionAmount;
        }

        $price = $this->client()->prices->retrieve($this->resolveTuitionPriceId());

        if (! is_int($price->unit_amount)) {
            throw new PaymentGatewayException('The tuition Price has no fixed unit amount.');
        }

        return $this->cachedTuitionAmount = $price->unit_amount;
    }

    /**
     * Create and finalize one monthly tuition invoice.
     *
     * The invoice is created as a draft with auto_advance off, its single line item is
     * attached explicitly, and it is finalized only once. A retry reuses the same draft
     * (idempotency key), never adds a second line item, and never finalizes twice.
     *
     * @param string $customerId The student's Stripe Customer ID.
     * @param int $amountMinor The amount in Stripe minor units.
     * @param string $description The line item and invoice description.
     * @param array<string, string> $metadata Metadata including student_id and billing_period.
     * @param int $dueAt The due date as a Unix timestamp.
     * @param string $idempotencyKey Stable key for this student and billing period.
     *
     * @return \Stripe\Invoice The finalized (open) invoice.
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function createMonthlyInvoice(
        string $customerId,
        int $amountMinor,
        string $description,
        array $metadata,
        int $dueAt,
        string $idempotencyKey,
    ): StripeInvoice {
        $stripe = $this->client();

        $invoiceParams = [
            'customer' => $customerId,
            'currency' => config('services.stripe.currency'),
            'collection_method' => self::COLLECTION_METHOD_SEND_INVOICE,
            'due_date' => $dueAt,
            'auto_advance' => false,
            'description' => $description,
            'metadata' => $metadata,
        ];

        $invoice = $stripe->invoices->create($invoiceParams, [
            'idempotency_key' => self::idempotencyKey($idempotencyKey . '-invoice', $invoiceParams),
        ]);

        $existingItems = $stripe->invoiceItems->all([
            'invoice' => $invoice->id,
            'limit' => 1,
        ]);

        if (count($existingItems->data) === 0) {
            $stripe->invoiceItems->create([
                'customer' => $customerId,
                'invoice' => $invoice->id,
                'amount' => $amountMinor,
                'currency' => config('services.stripe.currency'),
                'description' => $description,
                'metadata' => $metadata,
            ]);
        }

        // An idempotent replay returns the response saved at creation time, which may still say
        // "draft" after the invoice was finalized. Re-read the live status before finalizing.
        $invoice = $stripe->invoices->retrieve($invoice->id);

        if ($invoice->status === StripeInvoiceStatus::DRAFT->value) {
            $invoice = $stripe->invoices->finalizeInvoice($invoice->id);
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
     * Retrieve a legacy Stripe Subscription.
     *
     * Only used to read metadata on invoices from the old subscription model, so
     * those invoices still resolve to a student. Callers catch failures.
     *
     * @param string $subscriptionId The Stripe Subscription ID.
     *
     * @return \Stripe\Subscription
     */
    public function retrieveSubscription(string $subscriptionId): Subscription
    {
        return $this->client()->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Convert Stripe minor units to a decimal database amount.
     *
     * @param int $amount The Stripe amount in minor units.
     *
     * @return numeric-string
     */
    public function fromMinorUnits(int $amount): string
    {
        if (self::usesZeroDecimalMinorUnits(config('services.stripe.currency'))) {
            return number_format(
                $amount,
                (int) config('constant.stripe.money_scale'),
                '.',
                '',
            );
        }

        return bcdiv(
            (string) $amount,
            $this->minorUnitMultiplier(),
            (int) config('constant.stripe.money_scale'),
        );
    }

    /**
     * Convert a decimal database amount to Stripe minor units.
     *
     * @param numeric-string $amount The database amount.
     *
     * @return int
     */
    public function toMinorUnits(string $amount): int
    {
        if (self::usesZeroDecimalMinorUnits(config('services.stripe.currency'))) {
            return (int) $amount;
        }

        return (int) bcmul(
            $amount,
            $this->minorUnitMultiplier(),
            0,
        );
    }

    /**
     * Determine whether a currency uses zero-decimal minor units.
     *
     * @param string|null $currency The ISO currency code.
     *
     * @return bool
     */
    public static function usesZeroDecimalMinorUnits(?string $currency): bool
    {
        return is_string($currency)
            && in_array(strtolower($currency), self::ZERO_DECIMAL_CURRENCIES, true);
    }

    /**
     * Verify a Stripe signature and construct its webhook event.
     *
     * @param string $payload The raw webhook request body.
     * @param string $signatureHeader The Stripe-Signature header.
     *
     * @return \Stripe\Event
     */
    public function constructWebhookEvent(
        string $payload,
        string $signatureHeader,
    ): \Stripe\Event {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $signatureHeader,
            config('services.stripe.webhook_secret'),
        );
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

    /**
     * Return the configured multiplier for two-decimal currencies.
     *
     * @return numeric-string
     */
    private function minorUnitMultiplier(): string
    {
        /** @var numeric-string $multiplier */
        $multiplier = (string) config('constant.stripe.minor_unit_multiplier');

        return $multiplier;
    }
}
