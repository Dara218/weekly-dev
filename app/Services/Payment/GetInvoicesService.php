<?php

namespace App\Services\Payment;

use App\Enum\{
    InvoiceStatus,
    UserRole,
};
use App\Interfaces\InvoiceInterface;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class GetInvoicesService
{
    /**
     * Initialize the invoice listing service.
     *
     * @param \App\Interfaces\InvoiceInterface $invoiceRepository The invoice repository.
     */
    public function __construct(
        private readonly InvoiceInterface $invoiceRepository,
    ) {
    }

    /**
     * Return invoices visible to the authenticated student or parent.
     *
     * @param \App\Models\User $user The authenticated payer.
     *
     * @param int $perPage The requested number of invoices per page.
     * @param int|null $studentId Optional linked student to filter a parent's invoices by.
     *
     * @return array{
     *     data: list<array<string, bool|int|string|null>>,
     *     meta: array{current_page: int, last_page: int, per_page: int, total: int}
     * }
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user, int $perPage, ?int $studentId = null): array
    {
        $perPage = $this->normalizePerPage($perPage);

        if ($user->role === UserRole::STUDENT->value) {
            return $this->studentInvoices($user, $perPage);
        }

        if ($user->role !== UserRole::PARENT->value) {
            return $this->emptyResult($perPage);
        }

        return $this->parentInvoices($user, $perPage, $studentId);
    }

    /**
     * Return invoices belonging to the user's student profile.
     *
     * @param \App\Models\User $user The authenticated student user.
     * @param int $perPage The number of invoices per page.
     *
     * @return array{
     *     data: list<array<string, bool|int|string|null>>,
     *     meta: array{current_page: int, last_page: int, per_page: int, total: int}
     * }
     */
    private function studentInvoices(User $user, int $perPage): array
    {
        /** @var Student|null $student */
        $student = $user->student;

        if ($student === null) {
            return $this->emptyResult($perPage);
        }

        return $this->formatInvoices(
            $this->invoiceRepository->listForStudent($student->id, $perPage),
        );
    }

    /**
     * Return invoices belonging to the user's linked students.
     *
     * @param \App\Models\User $user The authenticated parent user.
     * @param int $perPage The number of invoices per page.
     * @param int|null $studentId Optional linked student to filter by.
     *
     * @return array{
     *     data: list<array<string, bool|int|string|null>>,
     *     meta: array{current_page: int, last_page: int, per_page: int, total: int}
     * }
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function parentInvoices(User $user, int $perPage, ?int $studentId): array
    {
        /** @var Parents|null $parent */
        $parent = $user->parent;
        $parentId = $parent?->id;

        if ($parent === null || $parentId === null) {
            return $this->emptyResult($perPage);
        }

        if ($studentId !== null) {
            if (! $this->parentOwnsStudent($parent, $studentId)) {
                throw new AuthorizationException('This student is not linked to your account.');
            }

            return $this->formatInvoices(
                $this->invoiceRepository->listForStudent($studentId, $perPage),
            );
        }

        return $this->formatInvoices(
            $this->invoiceRepository->listForParent($parentId, $perPage),
        );
    }

    /**
     * Format invoices for the Fees API response.
     *
     * @param LengthAwarePaginator<int, Invoice> $invoices The scoped invoices.
     *
     * @return array{
     *     data: list<array<string, bool|int|string|null>>,
     *     meta: array{current_page: int, last_page: int, per_page: int, total: int}
     * }
     */
    private function formatInvoices(LengthAwarePaginator $invoices): array
    {
        $scale = (int) config('constant.stripe.money_scale');
        $today = BillingSettings::now()->toDateString();
        $rows = [];

        foreach ($invoices->items() as $invoice) {
            $rows[] = $this->formatInvoice($invoice, $scale, $today);
        }

        return [
            'data' => $rows,
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ];
    }

    /**
     * Format one invoice with its paid and outstanding amounts.
     *
     * @param \App\Models\Invoice $invoice The invoice to format.
     * @param int $scale The database money scale.
     * @param string $today Today's date in the billing timezone (Y-m-d).
     *
     * @return array<string, bool|int|string|null>
     */
    private function formatInvoice(Invoice $invoice, int $scale, string $today): array
    {
        /** @var numeric-string $paidAmount */
        $paidAmount = (string) ($invoice->getAttribute('amount_paid') ?? '0');
        $amountPaid = bcadd($paidAmount, '0', $scale);
        $totalAmount = bcadd((string) $invoice->total_amount, '0', $scale);
        $outstanding = bcsub($totalAmount, $amountPaid, $scale);

        if (bccomp($outstanding, '0', $scale) === -1) {
            $outstanding = bcadd('0', '0', $scale);
        }

        // Fully settled, including invoices covered by customer-balance credit.
        if ($invoice->status === InvoiceStatus::PAID->value) {
            $outstanding = bcadd('0', '0', $scale);
        }

        /** @var Student|null $student */
        $student = $invoice->student;
        /** @var User|null $studentUser */
        $studentUser = $student?->user;
        $studentName = $studentUser === null
            ? null
            : implode(' ', array_filter([
                $studentUser->first_name,
                $studentUser->middle_name,
                $studentUser->last_name,
            ]));

        $canPay = $invoice->status === InvoiceStatus::UNPAID->value
            && is_string($invoice->stripe_hosted_invoice_url)
            && $invoice->stripe_hosted_invoice_url !== '';

        $dueDate = substr((string) $invoice->due_date, 0, 10);
        $isOverdue = $invoice->status === InvoiceStatus::UNPAID->value
            && $dueDate !== ''
            && $dueDate < $today;

        return [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'student_id' => $invoice->student_id,
            'student_name' => $studentName,
            'total_amount' => $totalAmount,
            'amount_paid' => $amountPaid,
            'outstanding' => $outstanding,
            'status' => $invoice->status,
            'due_date' => (string) $invoice->due_date,
            'stripe_invoice_id' => $invoice->stripe_invoice_id,
            'stripe_hosted_invoice_url' => $invoice->stripe_hosted_invoice_url,
            'can_pay' => $canPay,
            'is_overdue' => $isOverdue,
        ];
    }

    /**
     * Whether the parent is linked to the given student.
     *
     * Uses the already-loaded students relation when present (unit tests / eager loads),
     * otherwise a single exists() query.
     *
     * @param \App\Models\Parents $parent The parent profile.
     * @param int $studentId The student ID being filtered.
     *
     * @return bool
     */
    private function parentOwnsStudent(Parents $parent, int $studentId): bool
    {
        if ($parent->relationLoaded('students')) {
            return $parent->students->contains('id', $studentId);
        }

        return Student::query()
            ->whereKey($studentId)
            ->where('parent_id', $parent->id)
            ->exists();
    }

    /**
     * Clamp the requested page size to the configured range.
     *
     * @param int $perPage The requested number of invoices per page.
     *
     * @return int
     */
    private function normalizePerPage(int $perPage): int
    {
        $minimum = (int) config('constant.stripe.invoices.min_per_page');
        $maximum = (int) config('constant.stripe.invoices.max_per_page');

        return max($minimum, min($perPage, $maximum));
    }

    /**
     * Return an empty paginated invoice result.
     *
     * @param int $perPage The normalized number of invoices per page.
     *
     * @return array{
     *     data: list<array<string, bool|int|string|null>>,
     *     meta: array{current_page: int, last_page: int, per_page: int, total: int}
     * }
     */
    private function emptyResult(int $perPage): array
    {
        return [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
            ],
        ];
    }
}
