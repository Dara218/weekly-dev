<?php

namespace App\Repositories;

use App\Interfaces\PaymentInterface;
use App\Models\Payment;

class PaymentRepository extends BaseRepository implements PaymentInterface
{
    /**
     * Initialize the payment repository.
     *
     * @param \App\Models\Payment $model The payment model.
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByStripeInvoiceId(string $invoiceId): ?Payment
    {
        /** @var Payment|null $payment */
        $payment = $this->model
            ->newQuery()
            ->where('stripe_invoice_id', $invoiceId)
            ->first();

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Payment
    {
        /** @var Payment $payment */
        $payment = parent::create($data);

        return $payment;
    }
}
