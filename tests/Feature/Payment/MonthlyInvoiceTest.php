<?php

namespace Tests\Feature\Payment;

use App\Enum\InvoiceStatus;
use App\Exceptions\PaymentGatewayException;
use App\Models\{
    Invoice,
    Student,
    User,
};
use App\Notifications\{
    InvoiceDueSoon,
    MonthlyInvoiceCreated,
};
use App\Services\Payment\{
    BillingSettings,
    CreateMonthlyInvoiceService,
    StripeCheckoutGateway,
};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{
    DB,
    Notification,
};
use Mockery\MockInterface;
use Stripe\Invoice as StripeInvoice;
use Tests\Concerns\RequiresPhpunitMysql;
use Tests\TestCase;

/**
 * Database-backed tests for monthly invoices, the scheduled commands and reminders.
 *
 * Stripe is mocked. Runs only against a dedicated MySQL database whose name ends in _phpunit.
 * Uses DatabaseTransactions so each test rolls back instead of refreshing the schema.
 * See docs/Stripe Checkout Implementation Guide.md (section 11).
 */
class MonthlyInvoiceTest extends TestCase
{
    use RequiresPhpunitMysql;

    /**
     * Skip unless the dedicated phpunit MySQL database is configured, then open fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRequiresPhpunitMysql();
    }

    /**
     * Creating the same student and billing period twice reuses one unpaid invoice row.
     */
    public function testItCreatesOneInvoicePerStudentAndPeriod(): void
    {
        [$studentId] = $this->seedFamily();
        $this->mockStripe(function (MockInterface $mock): void {
            $mock->shouldReceive('createMonthlyInvoice')
                ->once()
                ->andReturn($this->stripeInvoice('in_1'));
        });

        $student = Student::query()->with(['user', 'parent.user'])->findOrFail($studentId);
        $service = app(CreateMonthlyInvoiceService::class);

        $first = $service->handle($student, '2026-12');
        $second = $service->handle($student, '2026-12');

        self::assertTrue($first->wasRecentlyCreated);
        self::assertFalse($second->wasRecentlyCreated);
        self::assertSame($first->id, $second->id);
        self::assertSame(1, Invoice::query()->count());
        self::assertSame('in_1', $first->stripe_invoice_id);
        self::assertSame('2026-12', $first->billing_period);
        self::assertSame(InvoiceStatus::UNPAID->value, $first->status);
    }

    /**
     * A Stripe failure during create must not leave a local invoices row behind.
     */
    public function testItLeavesNoLocalRowWhenStripeFails(): void
    {
        [$studentId] = $this->seedFamily();
        $this->mockStripe(function (MockInterface $mock): void {
            $mock->shouldReceive('createMonthlyInvoice')
                ->once()
                ->andThrow(new PaymentGatewayException('Stripe is down.'));
        });

        $student = Student::query()->with(['user', 'parent.user'])->findOrFail($studentId);

        try {
            app(CreateMonthlyInvoiceService::class)->handle($student, '2026-12');
            self::fail('Expected the Stripe failure to bubble up.');
        } catch (PaymentGatewayException) {
            // Expected: the command logs this per student and moves on.
        }

        self::assertSame(0, Invoice::query()->count());
    }

    /**
     * When the webhook mirrored the Stripe invoice first, the service claims that row
     * and marks it newly created only on the first claim.
     */
    public function testItClaimsTheRowTheWebhookMirroredFirst(): void
    {
        [$studentId] = $this->seedFamily();
        // The webhook mirrored the Stripe invoice (no billing period) before the service saved it.
        $this->insertInvoice($studentId, '2026-12-31', InvoiceStatus::UNPAID->value, '1');
        $mirroredId = (int) Invoice::query()->value('id');

        $this->mockStripe(function (MockInterface $mock): void {
            $mock->shouldReceive('createMonthlyInvoice')->andReturn($this->stripeInvoice('in_1'));
        });

        $student = Student::query()->with(['user', 'parent.user'])->findOrFail($studentId);
        $service = app(CreateMonthlyInvoiceService::class);

        $first = $service->handle($student, '2026-12');
        $second = $service->handle($student, '2026-12');

        self::assertSame($mirroredId, $first->id);
        self::assertTrue($first->wasRecentlyCreated);
        self::assertSame('2026-12', $first->billing_period);
        self::assertFalse($second->wasRecentlyCreated);
        self::assertSame(1, Invoice::query()->count());
    }

    /**
     * Generate notifies student and parent once even when the webhook mirrored the row first.
     */
    public function testTheCommandNotifiesOnceWhenTheWebhookMirroredTheInvoiceFirst(): void
    {
        Notification::fake();
        [$studentId, $studentUser, $parentUser] = $this->seedFamily('1');
        $this->insertInvoice($studentId, '2026-12-31', InvoiceStatus::UNPAID->value, '1');

        $this->mockStripe(function (MockInterface $mock): void {
            $mock->shouldReceive('createMonthlyInvoice')->andReturn($this->stripeInvoice('in_1'));
        });

        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertSuccessful();
        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertSuccessful();

        self::assertSame(1, Invoice::query()->count());
        Notification::assertSentTo(User::query()->findOrFail($studentUser), MonthlyInvoiceCreated::class);
        Notification::assertSentTo(User::query()->findOrFail($parentUser), MonthlyInvoiceCreated::class);
        Notification::assertSentTimes(MonthlyInvoiceCreated::class, 2);
    }

    /**
     * A voided invoice for the period still counts as existing, so generate skips and notifies nobody.
     */
    public function testTheGeneratorSkipsAPeriodWhoseInvoiceWasVoided(): void
    {
        Notification::fake();
        [$studentId] = $this->seedFamily('1');
        DB::table('invoices')->insert([
            'invoice_no' => 'INV-VOID',
            'student_id' => $studentId,
            'total_amount' => '10000.00',
            'due_date' => '2026-10-31',
            'status' => InvoiceStatus::VOID->value,
            'billing_period' => '2026-10',
            'stripe_invoice_id' => 'in_void_oct',
            'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/void',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->mockStripeWithCounter();

        $this->artisan('invoices:generate-monthly', ['--month' => '2026-10'])->assertSuccessful();

        self::assertSame(1, Invoice::query()->count());
        self::assertSame(InvoiceStatus::VOID->value, Invoice::query()->value('status'));
        Notification::assertNothingSent();
    }

    /**
     * Stripe due_at is end of the billing month at due-end time in the billing timezone.
     */
    public function testTheDueDateIsTheLastMomentOfTheMonthInManila(): void
    {
        [$studentId] = $this->seedFamily();
        $dueAt = null;

        $this->mockStripe(function (MockInterface $mock) use (&$dueAt): void {
            $mock->shouldReceive('createMonthlyInvoice')
                ->once()
                ->andReturnUsing(function (string $customerId, int $amountMinor, string $description, array $metadata, int $due, string $key) use (&$dueAt) {
                    $dueAt = $due;

                    return $this->stripeInvoice('in_due');
                });
        });

        $student = Student::query()->with(['user', 'parent.user'])->findOrFail($studentId);
        $invoice = app(CreateMonthlyInvoiceService::class)->handle($student, '2026-12');

        self::assertSame(
            BillingSettings::atDueEndOfDay(
                CarbonImmutable::create(2026, 12, 31, 0, 0, 0, BillingSettings::timezone()),
            )->getTimestamp(),
            $dueAt,
        );
        self::assertSame('2026-12-31', (string) $invoice->due_date);
    }

    /**
     * Generate only bills active students and notifies both the student and linked parent.
     */
    public function testGenerateCommandCreatesInvoicesForActiveStudentsOnlyAndNotifiesBothPayers(): void
    {
        Notification::fake();
        [$activeId, $activeUser, $parentUser] = $this->seedFamily('1');
        $this->seedFamily('0');
        $this->mockStripeWithCounter();

        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertSuccessful();

        self::assertSame(1, Invoice::query()->count());
        self::assertSame($activeId, Invoice::query()->value('student_id'));
        Notification::assertSentTo(User::query()->findOrFail($activeUser), MonthlyInvoiceCreated::class);
        Notification::assertSentTo(User::query()->findOrFail($parentUser), MonthlyInvoiceCreated::class);
    }

    /**
     * A second generate for the same month creates no new invoice and sends no extra notices.
     */
    public function testRerunningTheCommandCreatesNothingNewAndSendsNoSecondNotice(): void
    {
        Notification::fake();
        $this->seedFamily('1');
        $this->mockStripeWithCounter();

        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertSuccessful();
        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertSuccessful();

        self::assertSame(1, Invoice::query()->count());
        Notification::assertSentTimes(MonthlyInvoiceCreated::class, 2);
    }

    /**
     * One student failing Stripe customer creation does not block the rest of the generate run.
     */
    public function testAFailingStudentDoesNotStopTheRun(): void
    {
        Notification::fake();
        [$failingId] = $this->seedFamily('1');
        $this->seedFamily('1');

        $this->mock(StripeCheckoutGateway::class, function (MockInterface $mock) use ($failingId): void {
            $mock->shouldReceive('ensureStudentCustomer')
                ->andReturnUsing(function (Student $student) use ($failingId): string {
                    if ($student->id === $failingId) {
                        throw new PaymentGatewayException('Customer creation failed.');
                    }

                    return 'cus_'.$student->id;
                });
            $mock->shouldReceive('resolveTuitionAmount')->andReturn(1000000);
            $mock->shouldReceive('fromMinorUnits')->andReturn('10000.00');
            $mock->shouldReceive('createMonthlyInvoice')->once()->andReturn($this->stripeInvoice('in_ok'));
        });

        $this->artisan('invoices:generate-monthly', ['--month' => '2026-12'])->assertFailed();

        self::assertSame(1, Invoice::query()->count());
        self::assertNotSame($failingId, Invoice::query()->value('student_id'));
    }

    /**
     * An invalid --month value fails the command without creating invoices.
     */
    public function testTheCommandRejectsAMalformedMonth(): void
    {
        $this->artisan('invoices:generate-monthly', ['--month' => '2026-13'])->assertFailed();

        self::assertSame(0, Invoice::query()->count());
    }

    /**
     * Remind-due notifies student and parent once for an unpaid invoice in the reminder window.
     */
    public function testTheReminderIsSentOnceToBothPayersForAnUnpaidInvoiceDueInThreeDays(): void
    {
        Notification::fake();
        [$studentId, $studentUser, $parentUser] = $this->seedFamily('1');
        $this->insertInvoice($studentId, $this->manilaDateFromToday(3), InvoiceStatus::UNPAID->value);

        $this->artisan('invoices:remind-due')->assertSuccessful();
        $this->artisan('invoices:remind-due')->assertSuccessful();

        Notification::assertSentTo(User::query()->findOrFail($studentUser), InvoiceDueSoon::class);
        Notification::assertSentTo(User::query()->findOrFail($parentUser), InvoiceDueSoon::class);
        Notification::assertSentTimes(InvoiceDueSoon::class, 2);
        self::assertNotNull(Invoice::query()->value('reminded_at'));
    }

    /**
     * Paid invoices and unpaid invoices outside the reminder window are not reminded.
     */
    public function testThePaidAndNotDueYetInvoicesAreNotReminded(): void
    {
        Notification::fake();
        [$studentId] = $this->seedFamily('1');
        $this->insertInvoice($studentId, $this->manilaDateFromToday(3), InvoiceStatus::PAID->value);
        $this->insertInvoice($studentId, $this->manilaDateFromToday(2), InvoiceStatus::UNPAID->value, 'INV-B');

        $this->artisan('invoices:remind-due')->assertSuccessful();

        Notification::assertNothingSent();
    }

    /**
     * Stub the gateway so every call succeeds with a fresh Stripe invoice ID.
     */
    private function mockStripeWithCounter(): void
    {
        $counter = 0;

        $this->mockStripe(function (MockInterface $mock) use (&$counter): void {
            $mock->shouldReceive('createMonthlyInvoice')->andReturnUsing(function () use (&$counter) {
                $counter++;

                return $this->stripeInvoice('in_'.$counter);
            });
        });
    }

    /**
     * Register a gateway double that succeeds for customers and amounts.
     *
     * @param callable(\Mockery\MockInterface): void $configure Extra expectations.
     */
    private function mockStripe(callable $configure): void
    {
        $this->mock(StripeCheckoutGateway::class, function (MockInterface $mock) use ($configure): void {
            $mock->shouldReceive('ensureStudentCustomer')->andReturnUsing(
                fn (Student $student): string => 'cus_'.$student->id,
            );
            $mock->shouldReceive('resolveTuitionAmount')->andReturn(1000000);
            $mock->shouldReceive('fromMinorUnits')->andReturn('10000.00');

            $configure($mock);
        });
    }

    /**
     * Build a Stripe invoice object as the SDK returns it.
     *
     * @param string $id The Stripe invoice ID.
     *
     * @return \Stripe\Invoice
     */
    private function stripeInvoice(string $id): StripeInvoice
    {
        return StripeInvoice::constructFrom([
            'id' => $id,
            'number' => null,
            'total' => 1000000,
            'status' => 'open',
            'hosted_invoice_url' => 'https://invoice.stripe.com/i/'.$id,
        ]);
    }

    /**
     * A calendar date offset from today in the billing timezone, as stored in due_date.
     *
     * @param int $days The number of days from today.
     *
     * @return string
     */
    private function manilaDateFromToday(int $days): string
    {
        return BillingSettings::now()->addDays($days)->toDateString();
    }

    /**
     * Seed a parent, its user, and one child with a user.
     *
     * @param string $status The raw student status ('1' active, '0' inactive).
     *
     * @return array{0: int, 1: int, 2: int} [student ID, student user ID, parent user ID]
     */
    private function seedFamily(string $status = '1'): array
    {
        $parentUserId = $this->insertUser('PARENT', 'p'.uniqid().'@test.local', 'Pat');
        $parentId = DB::table('parents')->insertGetId([
            'user_id' => $parentUserId,
            'phone' => '123',
            'occupation' => 'Job',
            'address' => 'Somewhere',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = $this->insertUser('STUDENT', 's'.uniqid().'@test.local', 'Ana');
        $studentId = DB::table('students')->insertGetId([
            'user_id' => $studentUserId,
            'parent_id' => $parentId,
            'admission_no' => 'ADM-'.$studentUserId,
            'class_id' => 1,
            'section_id' => 1,
            'gender' => 'FEMALE',
            'dob' => '2010-05-15',
            'address' => 'Somewhere',
            'student_status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$studentId, $studentUserId, $parentUserId];
    }

    /**
     * Insert an unpaid or paid invoice row for a student.
     *
     * @param int $studentId The student ID.
     * @param string $dueDate The due date (Y-m-d).
     * @param string $status The invoice status.
     * @param string $number The invoice number.
     */
    private function insertInvoice(int $studentId, string $dueDate, string $status, string $number = 'INV-A'): void
    {
        DB::table('invoices')->insert([
            'invoice_no' => $number,
            'student_id' => $studentId,
            'total_amount' => '10000.00',
            'due_date' => $dueDate,
            'status' => $status,
            'stripe_invoice_id' => 'in_'.$number,
            'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/'.$number,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Insert a minimal users row for fixtures.
     *
     * @param string $role The user role value.
     * @param string $email The unique email.
     * @param string $first The first name.
     *
     * @return int The user ID.
     */
    private function insertUser(string $role, string $email, string $first): int
    {
        return DB::table('users')->insertGetId([
            'first_name' => $first,
            'middle_name' => 'Mid',
            'last_name' => 'Test',
            'email' => $email,
            'password' => 'x',
            'role' => $role,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
