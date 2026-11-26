<?php

namespace App\Services\Payment;

use App\Enum\{
    InvoiceStatus,
    PaymentMethod,
    StripeInvoiceStatus,
};
use App\Exceptions\UnresolvedStripeInvoiceException;
use App\Interfaces\PaymentInterface;
use App\Models\{
    Invoice,
    Payment,
    Student,
};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\InvalidRequestException;
use Stripe\Invoice as StripeInvoice;

class SyncStripeInvoiceService
{
    /**
     * Initialize the Stripe invoice sync service.
     *
     * @param \App\Interfaces\PaymentInterface $paymentRepository The payment repository.
     * @param \App\Services\Payment\StripeCheckoutGateway $gateway The Stripe gateway.
     */
    public function __construct(
        private readonly PaymentInterface $paymentRepository,
        private readonly StripeCheckoutGateway $gateway,
    ) {
    }

    /**
     * Fetch the current invoice from Stripe and mirror it locally.
     *
     * Webhook payloads can be stale or out of order, so the live object is the source of truth.
     * A deleted draft (404) is ignored.
     *
     * @param string $stripeInvoiceId The Stripe Invoice ID from the event.
     *
     * @return void
     */
    public function syncById(string $stripeInvoiceId): void
    {
        try {
            $stripeInvoice = $this->gateway->retrieveInvoice($stripeInvoiceId);
        } catch (InvalidRequestException $error) {
            if ($error->getHttpStatus() === 404) {
                return;
            }

            throw $error;
        }

        $this->sync($stripeInvoice);
    }

    /**
     * Upsert a local invoice mirror from a Stripe Invoice object.
     *
     * @param \Stripe\Invoice $stripeInvoice The Stripe invoice.
     *
     * @return void
     *
     * @throws \App\Exceptions\UnresolvedStripeInvoiceException
     */
    public function sync(StripeInvoice $stripeInvoice): void
    {
        $student = $this->resolveStudent($stripeInvoice);

        if (! $student instanceof Student) {
            // Not a subscription invoice (e.g. a manual Dashboard invoice). Nothing to mirror.
            return;
        }

        // Face value of the invoice (after discounts and tax).
        $totalCents = (int) ($stripeInvoice->total ?: $stripeInvoice->amount_due ?: 0);
        $total = $this->gateway->fromMinorUnits($totalCents);
        // What was actually charged. Can be less than total when customer-balance credits apply.
        $paid = $this->gateway->fromMinorUnits((int) ($stripeInvoice->amount_paid ?? 0));
        $status = $this->mapStatus((string) $stripeInvoice->status);
        $invoiceNo = is_string($stripeInvoice->number) && $stripeInvoice->number !== ''
            ? $stripeInvoice->number
            : $stripeInvoice->id;
        $timezone = BillingSettings::timezone();
        $dueDate = $stripeInvoice->due_date
            ? CarbonImmutable::createFromTimestamp((int) $stripeInvoice->due_date, $timezone)
                ->toDateString()
            : ($stripeInvoice->period_end
                ? CarbonImmutable::createFromTimestamp((int) $stripeInvoice->period_end, $timezone)
                    ->toDateString()
                : BillingSettings::now()->toDateString());
        $hostedUrl = is_string($stripeInvoice->hosted_invoice_url)
            ? $stripeInvoice->hosted_invoice_url
            : null;

        $scale = (int) config('constant.stripe.money_scale');

        // Unique-constraint errors propagate on purpose: the webhook then returns 500
        // and Stripe retries, instead of acknowledging the event and dropping the update.
        DB::transaction(function () use (
            $stripeInvoice,
            $student,
            $total,
            $paid,
            $status,
            $invoiceNo,
            $dueDate,
            $hostedUrl,
            $scale,
        ): void {
            $invoice = Invoice::query()
                ->where('stripe_invoice_id', $stripeInvoice->id)
                ->lockForUpdate()
                ->first() ?? new Invoice([
                    'student_id' => $student->id,
                    'stripe_invoice_id' => $stripeInvoice->id,
                ]);

            // Webhooks can arrive out of order. Never move a settled invoice back to unpaid.
            $nextStatus = $invoice->status === InvoiceStatus::PAID->value
                && $status === InvoiceStatus::UNPAID->value
                    ? InvoiceStatus::PAID->value
                    : $status;

            $invoice->fill([
                'invoice_no' => $invoiceNo,
                'total_amount' => $total,
                'due_date' => $dueDate,
                'status' => $nextStatus,
                'stripe_hosted_invoice_url' => $hostedUrl,
            ]);
            $invoice->save();

            if ($nextStatus === InvoiceStatus::PAID->value && bccomp($paid, '0', $scale) === 1) {
                $this->recordPaymentIfNeeded($invoice, $stripeInvoice, $paid);
            }
        });
    }

    /**
     * Resolve the local student for a subscription invoice.
     *
     * Returns null for invoices that are not subscription invoices. Throws when a
     * subscription invoice cannot be linked yet so the webhook returns 500 and Stripe
     * retries, instead of acknowledging the event and losing the invoice.
     *
     * @param \Stripe\Invoice $stripeInvoice The Stripe invoice.
     *
     * @return \App\Models\Student|null
     *
     * @throws \App\Exceptions\UnresolvedStripeInvoiceException
     */
    private function resolveStudent(StripeInvoice $stripeInvoice): ?Student
    {
        $studentId = $this->resolveStudentId($stripeInvoice);

        if ($studentId === null) {
            if ($this->resolveSubscriptionId($stripeInvoice) === null) {
                return null;
            }

            throw new UnresolvedStripeInvoiceException(
                'Stripe invoice ' . $stripeInvoice->id . ' has no resolvable student.',
            );
        }

        $student = Student::query()->find($studentId);

        if (! $student instanceof Student) {
            throw new UnresolvedStripeInvoiceException(
                'Stripe invoice ' . $stripeInvoice->id . ' references missing student ' . $studentId . '.',
            );
        }

        return $student;
    }

    /**
     * Record an ONLINE payment for a paid Stripe invoice when missing.
     *
     * @param \App\Models\Invoice $invoice The local invoice.
     * @param \Stripe\Invoice $stripeInvoice The Stripe invoice.
     * @param numeric-string $amount The paid amount.
     *
     * @return void
     */
    private function recordPaymentIfNeeded(
        Invoice $invoice,
        StripeInvoice $stripeInvoice,
        string $amount,
    ): void {
        $existing = $this->paymentRepository->findByStripeInvoiceId((string) $stripeInvoice->id);

        if ($existing instanceof Payment) {
            return;
        }

        $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'amount_paid' => $amount,
            'method' => PaymentMethod::ONLINE->value,
            'paid_at' => now(),
            'stripe_invoice_id' => $stripeInvoice->id,
            'created_at' => now(),
        ]);
    }

    /**
     * Map a Stripe invoice status onto the local enum.
     *
     * @param string $stripeStatus The Stripe status.
     *
     * @return string
     */
    private function mapStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            StripeInvoiceStatus::PAID->value => InvoiceStatus::PAID->value,
            StripeInvoiceStatus::VOID->value => InvoiceStatus::VOID->value,
            StripeInvoiceStatus::UNCOLLECTIBLE->value => InvoiceStatus::UNCOLLECTIBLE->value,
            // draft and open remain payable once they have a hosted URL.
            default => InvoiceStatus::UNPAID->value,
        };
    }

    /**
     * Resolve the local student ID from invoice or subscription metadata.
     *
     * Stripe API 2025+ removed Invoice.subscription. Use
     * parent.subscription_details instead, with a legacy fallback.
     *
     * @param \Stripe\Invoice $stripeInvoice The Stripe invoice.
     *
     * @return int|null
     */
    private function resolveStudentId(StripeInvoice $stripeInvoice): ?int
    {
        $fromInvoice = (int) ($stripeInvoice->metadata->student_id ?? 0);

        if ($fromInvoice > 0) {
            return $fromInvoice;
        }

        $parentDetails = $stripeInvoice->parent->subscription_details ?? null;
        $fromParentMeta = (int) ($parentDetails->metadata->student_id ?? 0);

        if ($fromParentMeta > 0) {
            return $fromParentMeta;
        }

        $subscriptionId = $this->resolveSubscriptionId($stripeInvoice);

        if ($subscriptionId === null) {
            return null;
        }

        $student = Student::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();

        if ($student instanceof Student) {
            return $student->id;
        }

        try {
            $subscription = $this->gateway->retrieveSubscription($subscriptionId);
            $fromSubscription = (int) ($subscription->metadata->student_id ?? 0);

            return $fromSubscription > 0 ? $fromSubscription : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve the Stripe Subscription ID from a modern or legacy Invoice shape.
     *
     * @param \Stripe\Invoice $stripeInvoice The Stripe invoice.
     *
     * @return string|null
     */
    private function resolveSubscriptionId(StripeInvoice $stripeInvoice): ?string
    {
        $parentSubscription = $stripeInvoice->parent->subscription_details->subscription ?? null;

        if (is_string($parentSubscription) && $parentSubscription !== '') {
            return $parentSubscription;
        }

        if (is_object($parentSubscription) && isset($parentSubscription->id)) {
            return (string) $parentSubscription->id;
        }

        // Legacy Invoice.subscription (pre-Basil API versions).
        $legacySubscription = $stripeInvoice->subscription ?? null;

        if (is_string($legacySubscription) && $legacySubscription !== '') {
            return $legacySubscription;
        }

        if (is_object($legacySubscription) && isset($legacySubscription->id)) {
            return (string) $legacySubscription->id;
        }

        return null;
    }
}
