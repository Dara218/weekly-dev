<?php

namespace App\Console\Commands;

use App\Enum\StudentStatus;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use App\Notifications\MonthlyInvoiceCreated;
use App\Services\Common\LogService;
use App\Services\Payment\{
    BillingSettings,
    CreateMonthlyInvoiceService,
};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The console command name and options.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly
                            {--month= : Billing period as YYYY-MM (defaults to current billing-timezone month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create each active student\'s tuition invoice for the month.';

    /**
     * Execute the console command.
     *
     * @param \App\Services\Payment\CreateMonthlyInvoiceService $invoiceService The invoice creation service.
     *
     * @return int
     */
    public function handle(CreateMonthlyInvoiceService $invoiceService): int
    {
        $period = (string) ($this->option('month')
            ?: BillingSettings::now()->format('Y-m'));

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period) !== 1) {
            $this->error('The --month option must be formatted as YYYY-MM.');

            return self::FAILURE;
        }

        $created = 0;
        $existing = 0;
        $failed = 0;

        Student::query()
            ->where('student_status', StudentStatus::ACTIVE->value)
            ->with(['user', 'parent.user'])
            ->orderBy('id')
            ->chunkById(BillingSettings::commandChunkSize(), function ($students) use (
                $invoiceService,
                $period,
                &$created,
                &$existing,
                &$failed,
            ): void {
                foreach ($students as $student) {
                    try {
                        $invoice = $invoiceService->handle($student, $period);
                    } catch (Throwable $error) {
                        $failed++;
                        LogService::error('Monthly invoice failed.', [
                            'student_id' => $student->id,
                            'billing_period' => $period,
                            'error' => $error->getMessage(),
                        ]);

                        continue;
                    }

                    if (! $invoice->wasRecentlyCreated) {
                        $existing++;

                        continue;
                    }

                    $created++;
                    $this->notifyPayers($student, $invoice);
                }
            });

        $this->info("Billing period {$period}: created {$created}, already existed {$existing}, failed {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Tell the student and the linked parent about a new invoice.
     *
     * A notification failure is logged but does not undo the invoice.
     *
     * @param \App\Models\Student $student The student (with user and parent.user loaded).
     * @param \App\Models\Invoice $invoice The newly created invoice.
     *
     * @return void
     */
    private function notifyPayers(Student $student, Invoice $invoice): void
    {
        $invoice->setRelation('student', $student);

        try {
            Notification::send($this->recipients($student), new MonthlyInvoiceCreated($invoice));
        } catch (Throwable $error) {
            LogService::error('Monthly invoice notification failed.', [
                'invoice_id' => $invoice->id,
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * The unique users who should hear about a student's invoices.
     *
     * @param \App\Models\Student $student The student.
     *
     * @return list<\App\Models\User>
     */
    private function recipients(Student $student): array
    {
        $users = [];

        /** @var User|null $studentUser */
        $studentUser = $student->user;
        /** @var Parents|null $parent */
        $parent = $student->parent;

        foreach ([$studentUser, $parent?->user] as $user) {
            if ($user instanceof User) {
                $users[$user->id] = $user;
            }
        }

        return array_values($users);
    }
}
