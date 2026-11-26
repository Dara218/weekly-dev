<?php

namespace App\Services\Payment;

use Carbon\CarbonImmutable;

/**
 * Billing calendar settings (timezone, due end-of-day, grace and reminder windows).
 */
final class BillingSettings
{
    /** Fallback when config is missing or empty. */
    private const DEFAULT_TIMEZONE = 'Asia/Manila';

    /**
     * Timezone used for billing periods, due dates, and overdue checks.
     *
     * @return string
     */
    public static function timezone(): string
    {
        $timezone = config('constant.stripe.billing_timezone', self::DEFAULT_TIMEZONE);

        return is_string($timezone) && $timezone !== ''
            ? $timezone
            : self::DEFAULT_TIMEZONE;
    }

    /**
     * Current instant in the billing timezone.
     *
     * @return \Carbon\CarbonImmutable
     */
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone());
    }

    /**
     * Days after today for consolidated / reissued overdue invoice due dates.
     *
     * @return int
     */
    public static function overdueGraceDays(): int
    {
        return max(1, (int) config('constant.stripe.overdue_grace_days', 7));
    }

    /**
     * Days before due date when the remind-due command sends a notice.
     *
     * @return int
     */
    public static function reminderDaysBeforeDue(): int
    {
        return max(1, (int) config('constant.stripe.reminder_days_before_due', 3));
    }

    /**
     * Chunk size for monthly generate and remind-due commands.
     *
     * @return int
     */
    public static function commandChunkSize(): int
    {
        return max(1, (int) config('constant.stripe.command_chunk_size', 100));
    }

    /**
     * Daily schedule time for invoices:generate-monthly (H:i in billing timezone).
     *
     * @return string
     */
    public static function generateAt(): string
    {
        $time = config('constant.stripe.schedule.generate_at', '00:05');

        return is_string($time) && $time !== '' ? $time : '00:05';
    }

    /**
     * Daily schedule time for invoices:remind-due (H:i in billing timezone).
     *
     * @return string
     */
    public static function remindAt(): string
    {
        $time = config('constant.stripe.schedule.remind_at', '08:00');

        return is_string($time) && $time !== '' ? $time : '08:00';
    }

    /**
     * Set the invoice due clock to end of day in the billing timezone.
     *
     * @param \Carbon\CarbonImmutable $date The calendar day.
     *
     * @return \Carbon\CarbonImmutable
     */
    public static function atDueEndOfDay(CarbonImmutable $date): CarbonImmutable
    {
        return $date->setTime(
            (int) config('constant.stripe.due_end.hour', 23),
            (int) config('constant.stripe.due_end.minute', 59),
            (int) config('constant.stripe.due_end.second', 59),
        );
    }
}
