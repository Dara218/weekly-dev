<?php

namespace App\Interfaces;

use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceInterface
{
    /**
     * Retrieve invoices belonging to a student.
     *
     * @param int $studentId The student profile ID.
     * @param int $perPage The number of invoices per page.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Invoice>
     */
    public function listForStudent(int $studentId, int $perPage): LengthAwarePaginator;

    /**
     * Retrieve invoices for every student linked to a parent.
     *
     * @param int $parentId The parent profile ID.
     * @param int $perPage The number of invoices per page.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Invoice>
     */
    public function listForParent(int $parentId, int $perPage): LengthAwarePaginator;

    /**
     * Find an invoice by its Stripe Invoice ID.
     *
     * @param string $stripeInvoiceId The Stripe Invoice ID.
     *
     * @return \App\Models\Invoice|null
     */
    public function findByStripeInvoiceId(string $stripeInvoiceId): ?Invoice;
}
