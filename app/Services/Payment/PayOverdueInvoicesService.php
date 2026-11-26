<?php

namespace App\Services\Payment;

use App\Enum\{
    InvoiceStatus,
    StripeInvoiceStatus,
    UserRole,
};
use App\Exceptions\PaymentGatewayException;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use App\Services\Common\LogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class PayOverdueInvoicesService
{
    /** Stripe line description prefix for each overdue original on a combined invoice. */
    private const OVERDUE_DESCRIPTION_PREFIX = 'Overdue tuition for ';

    /** Stripe invoice/line description prefix used when re-issuing a voided original. */
    private const TUITION_DESCRIPTION_PREFIX = 'Tuition fee for ';

    /** Suffix appended to re-issued tuition descriptions. */
    private const REISSUED_SUFFIX = ' (reissued)';

    /** Fallback local invoice number prefix for combined overdue invoices. */
    private const COMBINED_INVOICE_NO_PREFIX = 'INV-COMBINED-';

    /** Stripe idempotency key prefix for consolidating overdue invoices. */
    private const CONSOLIDATE_IDEMPOTENCY_PREFIX = 'consolidate-student-';

    /** Stripe idempotency key prefix for re-issuing a voided original. */
    private const REISSUE_IDEMPOTENCY_PREFIX = 'reissue-student-';

    /** Carbon format for the human-readable billing period label. */
    private const PERIOD_LABEL_FORMAT = 'F Y';

    /**
     * Initialize the pay-overdue service.
     *
     * @param \App\Services\Payment\StripeCheckoutGateway $gateway The Stripe gateway.
     * @param \App\Services\Payment\StripeInvoiceOperations $invoices Stripe invoice lifecycle helpers.
     */
    public function __construct(
        private readonly StripeCheckoutGateway $gateway,
        private readonly StripeInvoiceOperations $invoices,
    ) {
    }

    /**
     * Return a hosted Stripe URL for the student's overdue unpaid invoices.
     *
     * One overdue invoice returns its own hosted URL. Two or more are replaced by one
     * combined invoice due today + 7 days, and the originals are voided.
     *
     * @param \App\Models\User $user The authenticated student or parent.
     * @param int|null $studentId Optional linked child for a parent.
     *
     * @return array{url: string}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Exceptions\PaymentGatewayException
     */
    public function handle(User $user, ?int $studentId = null): array
    {
        $student = $this->resolveStudent($user, $studentId);

        // Lock only long enough to read the current overdue set. Stripe calls stay outside
        // the transaction so a compensation save is not rolled back with the error.
        $overdue = DB::transaction(function () use ($student): Collection {
            Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();

            return $this->overdueInvoices($student->id);
        });

        if ($overdue->isEmpty()) {
            throw ValidationException::withMessages([
                'invoice' => 'There are no overdue invoices to pay.',
            ]);
        }

        if ($overdue->count() === 1) {
            /** @var Invoice $only */
            $only = $overdue->first();

            if (
                ! is_string($only->stripe_hosted_invoice_url)
                || $only->stripe_hosted_invoice_url === ''
            ) {
                throw ValidationException::withMessages([
                    'invoice' => 'This invoice has no Stripe payment page yet. Wait for the webhook or refresh Fees.',
                ]);
            }

            return ['url' => $only->stripe_hosted_invoice_url];
        }

        return $this->consolidate($student, $overdue->all());
    }

    /**
     * Replace the overdue invoices with one combined Stripe invoice.
     *
     * @param \App\Models\Student $student The student being billed.
     * @param array<int, \App\Models\Invoice> $overdue The overdue unpaid invoices, oldest first.
     *
     * @return array{url: string}
     *
     * @throws \App\Exceptions\PaymentGatewayException
     * @throws \Illuminate\Validation\ValidationException
     */
    private function consolidate(Student $student, array $overdue): array
    {
        $customerId = $this->gateway->ensureStudentCustomer($student);
        $timezone = BillingSettings::timezone();
        $dueAt = BillingSettings::atDueEndOfDay(
            BillingSettings::now()->addDays(BillingSettings::overdueGraceDays()),
        );

        $originalStripeIds = [];
        $lines = [];

        foreach ($overdue as $invoice) {
            if (! is_string($invoice->stripe_invoice_id) || $invoice->stripe_invoice_id === '') {
                throw ValidationException::withMessages([
                    'invoice' => 'An overdue invoice is missing its Stripe id and cannot be combined.',
                ]);
            }

            $originalStripeIds[] = $invoice->stripe_invoice_id;
            $label = is_string($invoice->billing_period) && $invoice->billing_period !== ''
                ? CarbonImmutable::parse($invoice->billing_period . '-01', $timezone)
                    ->format(self::PERIOD_LABEL_FORMAT)
                : (string) $invoice->invoice_no;

            $lines[] = [
                'amount_minor' => $this->gateway->toMinorUnits((string) $invoice->total_amount),
                'description' => self::OVERDUE_DESCRIPTION_PREFIX . $label,
                'metadata' => [
                    'student_id' => (string) $student->id,
                    'original_invoice_id' => (string) $invoice->id,
                    'original_stripe_invoice_id' => $invoice->stripe_invoice_id,
                ],
            ];
        }

        sort($originalStripeIds);
        $idempotencyBase = self::CONSOLIDATE_IDEMPOTENCY_PREFIX
            . $student->id
            . '-'
            . implode('-', $originalStripeIds);

        $draft = $this->invoices->createConsolidatedDraft(
            customerId: $customerId,
            lines: $lines,
            dueAt: $dueAt->getTimestamp(),
            metadata: [
                'student_id' => (string) $student->id,
                'consolidated' => '1',
                'original_stripe_invoice_ids' => implode(',', $originalStripeIds),
            ],
            idempotencyKey: $idempotencyBase,
        );

        $voided = [];

        try {
            foreach ($overdue as $invoice) {
                $live = $this->invoices->retrieveInvoice((string) $invoice->stripe_invoice_id);

                if ($live->status === StripeInvoiceStatus::PAID->value) {
                    throw new PaymentGatewayException(
                        'Stripe invoice ' . $invoice->stripe_invoice_id . ' was paid and cannot be combined.',
                    );
                }

                $this->invoices->voidInvoice((string) $invoice->stripe_invoice_id);
                $voided[] = $invoice;
            }
        } catch (Throwable $error) {
            $this->compensate($student, $customerId, $voided, (string) $draft->id);

            throw $error instanceof PaymentGatewayException
                ? $error
                : new PaymentGatewayException($error->getMessage(), 0, $error);
        }

        $combined = $this->invoices->finalizeInvoice((string) $draft->id);

        return DB::transaction(function () use ($student, $combined, $dueAt, $overdue): array {
            Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();

            // Another request may have finished first. Prefer the already-saved combined row.
            $alreadyCombined = Invoice::query()
                ->where('stripe_invoice_id', $combined->id)
                ->where('status', InvoiceStatus::UNPAID->value)
                ->whereNotNull('stripe_hosted_invoice_url')
                ->first();

            if (
                $alreadyCombined instanceof Invoice
                && Invoice::query()
                    ->whereIn('id', array_map(static fn (Invoice $invoice): int => $invoice->id, $overdue))
                    ->where('status', InvoiceStatus::VOID->value)
                    ->count() === count($overdue)
            ) {
                return ['url' => (string) $alreadyCombined->stripe_hosted_invoice_url];
            }

            $local = $this->saveCombinedInvoice($student, $combined, $dueAt->toDateString());

            Invoice::query()
                ->whereIn('id', array_map(static fn (Invoice $invoice): int => $invoice->id, $overdue))
                ->update([
                    'status' => InvoiceStatus::VOID->value,
                    'consolidated_into_id' => $local->id,
                ]);

            return ['url' => (string) $combined->hosted_invoice_url];
        });
    }

    /**
     * Re-issue open invoices for originals that were already voided, then delete the draft.
     *
     * @param \App\Models\Student $student The student.
     * @param string $customerId The Stripe customer id.
     * @param list<\App\Models\Invoice> $voided Originals that were voided before the failure.
     * @param string $draftId The combined draft Stripe invoice id.
     *
     * @return void
     */
    private function compensate(
        Student $student,
        string $customerId,
        array $voided,
        string $draftId,
    ): void {
        $failedReissues = [];

        foreach ($voided as $invoice) {
            try {
                $this->reissueOriginal($student, $customerId, $invoice);
            } catch (Throwable $error) {
                $failedReissues[] = [
                    'invoice_id' => $invoice->id,
                    'stripe_invoice_id' => $invoice->stripe_invoice_id,
                    'error' => $error->getMessage(),
                ];
            }
        }

        try {
            $this->invoices->deleteDraftInvoice($draftId);
        } catch (Throwable $error) {
            LogService::error('CRITICAL: Failed to delete consolidated draft after a partial void.', [
                'student_id' => $student->id,
                'draft_stripe_invoice_id' => $draftId,
                'error' => $error->getMessage(),
                'failed_reissues' => $failedReissues,
            ]);
        }

        if ($failedReissues !== []) {
            LogService::error('CRITICAL: Failed to re-issue overdue invoices after a partial void.', [
                'student_id' => $student->id,
                'draft_stripe_invoice_id' => $draftId,
                'failed_reissues' => $failedReissues,
            ]);
        }
    }

    /**
     * Create a replacement open Stripe invoice for a voided original and update the local row.
     *
     * @param \App\Models\Student $student The student.
     * @param string $customerId The Stripe customer id.
     * @param \App\Models\Invoice $invoice The original local row.
     *
     * @return void
     *
     * @throws \App\Exceptions\PaymentGatewayException
     */
    private function reissueOriginal(Student $student, string $customerId, Invoice $invoice): void
    {
        $timezone = BillingSettings::timezone();
        $billingPeriod = is_string($invoice->billing_period) && $invoice->billing_period !== ''
            ? $invoice->billing_period
            : CarbonImmutable::parse((string) $invoice->due_date, $timezone)->format('Y-m');

        // Stripe rejects past due dates. The original was overdue, so the replacement uses
        // the same grace window as a combined invoice.
        $dueAt = BillingSettings::atDueEndOfDay(
            BillingSettings::now()->addDays(BillingSettings::overdueGraceDays()),
        );
        $label = CarbonImmutable::parse($billingPeriod . '-01', $timezone)
            ->format(self::PERIOD_LABEL_FORMAT);
        $oldStripeId = (string) $invoice->stripe_invoice_id;

        $replacement = $this->gateway->createMonthlyInvoice(
            customerId: $customerId,
            amountMinor: $this->gateway->toMinorUnits((string) $invoice->total_amount),
            description: self::TUITION_DESCRIPTION_PREFIX . $label . self::REISSUED_SUFFIX,
            metadata: [
                'student_id' => (string) $student->id,
                'billing_period' => $billingPeriod,
                'reissued_from' => $oldStripeId,
            ],
            dueAt: $dueAt->getTimestamp(),
            idempotencyKey: self::REISSUE_IDEMPOTENCY_PREFIX . $student->id . '-' . $oldStripeId,
        );

        $invoice->forceFill([
            'invoice_no' => is_string($replacement->number) && $replacement->number !== ''
                ? $replacement->number
                : $invoice->invoice_no,
            'due_date' => $dueAt->toDateString(),
            'status' => InvoiceStatus::UNPAID->value,
            'stripe_invoice_id' => $replacement->id,
            'stripe_hosted_invoice_url' => $replacement->hosted_invoice_url,
            'consolidated_into_id' => null,
        ])->save();
    }

    /**
     * Upsert the combined local invoice by Stripe id (the webhook may have mirrored the draft).
     *
     * @param \App\Models\Student $student The student.
     * @param \Stripe\Invoice $stripeInvoice The finalized combined Stripe invoice.
     * @param string $dueDate The combined due date as YYYY-MM-DD.
     *
     * @return \App\Models\Invoice
     */
    private function saveCombinedInvoice(
        Student $student,
        \Stripe\Invoice $stripeInvoice,
        string $dueDate,
    ): Invoice {
        $attributes = [
            'student_id' => $student->id,
            'invoice_no' => is_string($stripeInvoice->number) && $stripeInvoice->number !== ''
                ? $stripeInvoice->number
                : self::COMBINED_INVOICE_NO_PREFIX . $student->id . '-' . str_replace('-', '', $dueDate),
            'total_amount' => $this->gateway->fromMinorUnits((int) $stripeInvoice->total),
            'due_date' => $dueDate,
            'status' => InvoiceStatus::UNPAID->value,
            'billing_period' => null,
            'stripe_hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
        ];

        $existing = Invoice::query()
            ->where('stripe_invoice_id', $stripeInvoice->id)
            ->first();

        if ($existing instanceof Invoice) {
            $existing->forceFill($attributes)->save();

            return $existing;
        }

        try {
            return Invoice::query()->create([
                ...$attributes,
                'stripe_invoice_id' => $stripeInvoice->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            $mirrored = Invoice::query()
                ->where('stripe_invoice_id', $stripeInvoice->id)
                ->firstOrFail();
            $mirrored->forceFill($attributes)->save();

            return $mirrored;
        }
    }

    /**
     * Overdue unpaid invoices for a student, oldest due date first.
     *
     * @param int $studentId The student id.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice>
     */
    private function overdueInvoices(int $studentId): Collection
    {
        $today = BillingSettings::now()->toDateString();

        return Invoice::query()
            ->where('student_id', $studentId)
            ->where('status', InvoiceStatus::UNPAID->value)
            ->whereNull('consolidated_into_id')
            ->whereNotNull('stripe_invoice_id')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * Resolve the student the authenticated user may pay for.
     *
     * @param \App\Models\User $user The authenticated user.
     * @param int|null $studentId Optional linked child for a parent.
     *
     * @return \App\Models\Student
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    private function resolveStudent(User $user, ?int $studentId): Student
    {
        if ($user->role === UserRole::STUDENT->value) {
            /** @var Student|null $student */
            $student = $user->student;

            if (! $student instanceof Student) {
                throw ValidationException::withMessages([
                    'invoice' => 'No student profile is linked to this account.',
                ]);
            }

            return $student;
        }

        if ($user->role !== UserRole::PARENT->value) {
            throw new AuthorizationException('Only students and parents can pay overdue invoices.');
        }

        /** @var Parents|null $parent */
        $parent = $user->parent;

        if (! $parent instanceof Parents) {
            throw new AuthorizationException('No parent profile is linked to this account.');
        }

        if ($studentId === null) {
            throw ValidationException::withMessages([
                'student_id' => 'A linked student_id is required.',
            ]);
        }

        $student = $this->linkedStudent($parent, $studentId);

        if (! $student instanceof Student) {
            throw new AuthorizationException('This student is not linked to your account.');
        }

        return $student;
    }

    /**
     * Resolve a student linked to the parent.
     *
     * Uses the already-loaded students relation when present, otherwise a scoped query.
     *
     * @param \App\Models\Parents $parent The parent profile.
     * @param int $studentId The requested student ID.
     *
     * @return \App\Models\Student|null
     */
    private function linkedStudent(Parents $parent, int $studentId): ?Student
    {
        if ($parent->relationLoaded('students')) {
            $student = $parent->students->firstWhere('id', $studentId);

            return $student instanceof Student ? $student : null;
        }

        $student = Student::query()
            ->whereKey($studentId)
            ->where('parent_id', $parent->id)
            ->first();

        return $student instanceof Student ? $student : null;
    }
}
