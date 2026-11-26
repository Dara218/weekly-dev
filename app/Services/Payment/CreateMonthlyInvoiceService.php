<?php

namespace App\Services\Payment;

use App\Enum\InvoiceStatus;
use App\Models\{
    Invoice,
    Student,
};
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

class CreateMonthlyInvoiceService
{
    /** Stripe invoice/line description prefix before the period label (e.g. "December 2026"). */
    private const DESCRIPTION_PREFIX = 'Tuition fee for ';

    /** Fallback local invoice number prefix when Stripe has not assigned a number yet. */
    private const INVOICE_NO_PREFIX = 'INV-';

    /** Stripe idempotency key prefix for monthly invoice creates. */
    private const IDEMPOTENCY_PREFIX = 'invoice-student-';

    /** Carbon format for the human-readable billing period label. */
    private const PERIOD_LABEL_FORMAT = 'F Y';

    /**
     * Initialize the monthly invoice service.
     *
     * @param \App\Services\Payment\StripeCheckoutGateway $gateway The Stripe gateway.
     */
    public function __construct(
        private readonly StripeCheckoutGateway $gateway,
    ) {
    }

    /**
     * Create the student's invoice for a billing period, unless it already exists.
     *
     * The returned invoice has `wasRecentlyCreated` set only when this call created it,
     * so callers send notifications exactly once.
     *
     * @param \App\Models\Student $student The student to bill (with user and parent.user loaded).
     * @param string $billingPeriod The period as YYYY-MM.
     *
     * @return \App\Models\Invoice
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function handle(Student $student, string $billingPeriod): Invoice
    {
        $existing = Invoice::query()
            ->where('student_id', $student->id)
            ->where('billing_period', $billingPeriod)
            ->first();

        if ($existing instanceof Invoice) {
            return $existing;
        }

        $timezone = BillingSettings::timezone();
        $period = BillingSettings::atDueEndOfDay(
            CarbonImmutable::parse($billingPeriod . '-01', $timezone)->endOfMonth(),
        );

        $customerId = $this->gateway->ensureStudentCustomer($student);
        $amount = $this->gateway->resolveTuitionAmount();
        $label = $period->format(self::PERIOD_LABEL_FORMAT);

        $stripeInvoice = $this->gateway->createMonthlyInvoice(
            customerId: $customerId,
            amountMinor: $amount,
            description: self::DESCRIPTION_PREFIX . $label,
            metadata: [
                'student_id' => (string) $student->id,
                'billing_period' => $billingPeriod,
            ],
            dueAt: $period->getTimestamp(),
            idempotencyKey: self::IDEMPOTENCY_PREFIX . $student->id . '-' . $billingPeriod,
        );

        // Stripe sends invoice.created and invoice.finalized the moment the invoice exists, so the
        // webhook may already have mirrored it. Claim that row instead of inserting a duplicate.
        $mirrored = $this->claimWebhookMirror((string) $stripeInvoice->id, $billingPeriod);

        if ($mirrored instanceof Invoice) {
            return $mirrored;
        }

        try {
            return Invoice::query()->firstOrCreate(
                [
                    'student_id' => $student->id,
                    'billing_period' => $billingPeriod,
                ],
                [
                    'invoice_no' => is_string($stripeInvoice->number) && $stripeInvoice->number !== ''
                        ? $stripeInvoice->number
                        : self::INVOICE_NO_PREFIX . str_replace('-', '', $billingPeriod) . '-' . $student->id,
                    'total_amount' => $this->gateway->fromMinorUnits((int) $stripeInvoice->total),
                    'due_date' => $period->toDateString(),
                    'status' => InvoiceStatus::UNPAID->value,
                    'stripe_invoice_id' => $stripeInvoice->id,
                    'stripe_hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                ],
            );
        } catch (UniqueConstraintViolationException $error) {
            // The webhook mirrored the invoice between the lookup above and this insert.
            $mirrored = $this->claimWebhookMirror((string) $stripeInvoice->id, $billingPeriod);

            if ($mirrored instanceof Invoice) {
                return $mirrored;
            }

            throw $error;
        }
    }

    /**
     * Find the row the webhook mirrored for a Stripe invoice and assign it to this billing period.
     *
     * The webhook creates rows without a billing period. The first time this runs for such a
     * row it is marked as newly created, so the caller sends the "new invoice" notification
     * exactly once. Later calls return the row unchanged.
     *
     * @param string $stripeInvoiceId The Stripe invoice ID.
     * @param string $billingPeriod The period as YYYY-MM.
     *
     * @return \App\Models\Invoice|null
     */
    private function claimWebhookMirror(string $stripeInvoiceId, string $billingPeriod): ?Invoice
    {
        $invoice = Invoice::query()->where('stripe_invoice_id', $stripeInvoiceId)->first();

        if (! $invoice instanceof Invoice) {
            return null;
        }

        if ($invoice->billing_period === null) {
            $invoice->forceFill(['billing_period' => $billingPeriod])->save();
            $invoice->wasRecentlyCreated = true;
        }

        return $invoice;
    }
}
