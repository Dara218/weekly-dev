<?php

namespace App\Providers;

use App\Interfaces\{
    InvoiceInterface,
    PaymentInterface,
};
use App\Repositories\{
    InvoiceRepository,
    PaymentRepository,
};
use Illuminate\Support\ServiceProvider;

class PaymentRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register payment repository bindings.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PaymentInterface::class, PaymentRepository::class);
        $this->app->bind(InvoiceInterface::class, InvoiceRepository::class);
    }
}
