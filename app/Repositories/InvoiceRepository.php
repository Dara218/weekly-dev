<?php

namespace App\Repositories;

use App\Interfaces\InvoiceInterface;
use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository extends BaseRepository implements InvoiceInterface
{
    /**
     * Initialize the invoice repository.
     *
     * @param \App\Models\Invoice $model The invoice model.
     */
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function listForStudent(int $studentId, int $perPage): LengthAwarePaginator
    {
        return Invoice::query()
            ->where('student_id', $studentId)
            ->whereNull('consolidated_into_id')
            ->with('student.user')
            ->withSum('payments as amount_paid', 'amount_paid')
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function listForParent(int $parentId, int $perPage): LengthAwarePaginator
    {
        return Invoice::query()
            ->whereHas('student', function ($query) use ($parentId): void {
                $query->where('parent_id', $parentId);
            })
            ->whereNull('consolidated_into_id')
            ->with('student.user')
            ->withSum('payments as amount_paid', 'amount_paid')
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findByStripeInvoiceId(string $stripeInvoiceId): ?Invoice
    {
        /** @var Invoice|null $invoice */
        $invoice = Invoice::query()
            ->where('stripe_invoice_id', $stripeInvoiceId)
            ->first();

        return $invoice;
    }
}
