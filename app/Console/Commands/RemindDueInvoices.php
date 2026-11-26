<?php

namespace App\Console\Commands;

use App\Enum\InvoiceStatus;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use App\Notifications\InvoiceDueSoon;
use App\Services\Common\LogService;
use App\Services\Payment\BillingSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

class RemindDueInvoices extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'invoices:remind-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind payers once about unpaid invoices due soon.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $dueDate = BillingSettings::now()
            ->addDays(BillingSettings::reminderDaysBeforeDue())
            ->toDateString();
        $reminded = 0;
        $failed = 0;

        Invoice::query()
            ->where('status', InvoiceStatus::UNPAID->value)
            ->whereNull('reminded_at')
            ->whereDate('due_date', $dueDate)
            ->with(['student.user', 'student.parent.user'])
            ->orderBy('id')
            ->chunkById(BillingSettings::commandChunkSize(), function ($invoices) use (&$reminded, &$failed): void {
                foreach ($invoices as $invoice) {
                    // Only rows still unpaid at send time, and only once.
                    $claimed = Invoice::query()
                        ->whereKey($invoice->id)
                        ->where('status', InvoiceStatus::UNPAID->value)
                        ->whereNull('reminded_at')
                        ->update(['reminded_at' => now()]);

                    if ($claimed === 0) {
                        continue;
                    }

                    try {
                        Notification::send($this->recipients($invoice), new InvoiceDueSoon($invoice));
                        $reminded++;
                    } catch (Throwable $error) {
                        $failed++;
                        // Release the claim so the next run can retry this reminder.
                        Invoice::query()->whereKey($invoice->id)->update(['reminded_at' => null]);
                        LogService::error('Invoice reminder failed.', [
                            'invoice_id' => $invoice->id,
                            'error' => $error->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Reminders sent: {$reminded}, failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * The unique users who should be reminded about an invoice.
     *
     * @param \App\Models\Invoice $invoice The invoice (with student.user and student.parent.user loaded).
     *
     * @return list<\App\Models\User>
     */
    private function recipients(Invoice $invoice): array
    {
        /** @var Student|null $student */
        $student = $invoice->student;
        $users = [];

        /** @var User|null $studentUser */
        $studentUser = $student?->user;
        /** @var Parents|null $parent */
        $parent = $student?->parent;

        foreach ([$studentUser, $parent?->user] as $user) {
            if ($user instanceof User) {
                $users[$user->id] = $user;
            }
        }

        return array_values($users);
    }
}
