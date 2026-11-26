<?php

use App\Services\Payment\BillingSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\{
    Artisan,
    Schedule,
};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$billingTimezone = BillingSettings::timezone();

// Tuition invoices: daily and idempotent, so a failed or late student is picked up on the next run.
Schedule::command('invoices:generate-monthly')
    ->dailyAt(BillingSettings::generateAt())
    ->timezone($billingTimezone);

Schedule::command('invoices:remind-due')
    ->dailyAt(BillingSettings::remindAt())
    ->timezone($billingTimezone);
