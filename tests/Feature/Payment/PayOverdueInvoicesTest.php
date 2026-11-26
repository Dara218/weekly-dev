<?php

namespace Tests\Feature\Payment;

use App\Enum\InvoiceStatus;
use App\Exceptions\PaymentGatewayException;
use App\Models\{
    Invoice,
    Student,
    User,
};
use App\Notifications\InvoiceDueSoon;
use App\Services\Payment\{
    BillingSettings,
    PayOverdueInvoicesService,
    StripeCheckoutGateway,
    StripeInvoiceOperations,
};
use Illuminate\Support\Facades\{
    DB,
    Notification,
};
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Stripe\Invoice as StripeInvoice;
use Tests\Concerns\RequiresPhpunitMysql;
use Tests\TestCase;

/**
 * Database-backed tests for consolidating overdue invoices into one payment.
 *
 * Stripe is mocked. Runs only against a dedicated MySQL database whose name ends in _phpunit.
 * Uses DatabaseTransactions so each test rolls back instead of refreshing the schema.
 */
class PayOverdueInvoicesTest extends TestCase
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
     * A single overdue invoice returns its existing hosted Stripe URL without consolidating.
     */
    public function testItReturnsTheHostedUrlWhenOnlyOneInvoiceIsOverdue(): void
    {
        [$studentId, $studentUserId] = $this->seedFamily();
        $this->insertOverdue($studentId, 'in_a', 'INV-A', -10);

        $result = app(PayOverdueInvoicesService::class)->handle(
            User::query()->findOrFail($studentUserId),
        );

        self::assertSame(['url' => 'https://invoice.stripe.com/i/in_a'], $result);
        self::assertSame(1, Invoice::query()->count());
        self::assertSame(InvoiceStatus::UNPAID->value, Invoice::query()->value('status'));
    }

    /**
     * Two or more overdue invoices become one combined unpaid invoice; originals are voided.
     */
    public function testItCombinesTwoOverdueInvoicesAndVoidsTheOriginals(): void
    {
        [$studentId, $studentUserId] = $this->seedFamily();
        $firstId = $this->insertOverdue($studentId, 'in_a', 'INV-A', -40, '2026-09');
        $secondId = $this->insertOverdue($studentId, 'in_b', 'INV-B', -10, '2026-10');

        $this->mockMoneyGateway();
        $this->mockInvoiceOps(function (MockInterface $mock): void {
            $mock->shouldReceive('createConsolidatedDraft')
                ->once()
                ->andReturn($this->stripeInvoice('in_combined', 'draft', null));
            $mock->shouldReceive('retrieveInvoice')
                ->with('in_a')
                ->andReturn($this->stripeInvoice('in_a', 'open'));
            $mock->shouldReceive('retrieveInvoice')
                ->with('in_b')
                ->andReturn($this->stripeInvoice('in_b', 'open'));
            $mock->shouldReceive('voidInvoice')->with('in_a')->once()->andReturn($this->stripeInvoice('in_a', 'void'));
            $mock->shouldReceive('voidInvoice')->with('in_b')->once()->andReturn($this->stripeInvoice('in_b', 'void'));
            $mock->shouldReceive('finalizeInvoice')
                ->once()
                ->with('in_combined')
                ->andReturn($this->stripeInvoice('in_combined', 'open', 'https://invoice.stripe.com/i/in_combined', 2000000));
        });

        $result = app(PayOverdueInvoicesService::class)->handle(
            User::query()->findOrFail($studentUserId),
        );

        self::assertSame(['url' => 'https://invoice.stripe.com/i/in_combined'], $result);

        $combined = Invoice::query()->where('stripe_invoice_id', 'in_combined')->firstOrFail();
        self::assertSame(InvoiceStatus::UNPAID->value, $combined->status);
        self::assertNull($combined->billing_period);
        self::assertSame('20000.00', (string) $combined->total_amount);
        self::assertSame(
            BillingSettings::now()->addDays(BillingSettings::overdueGraceDays())->toDateString(),
            (string) $combined->due_date,
        );

        $first = Invoice::query()->findOrFail($firstId);
        $second = Invoice::query()->findOrFail($secondId);
        self::assertSame(InvoiceStatus::VOID->value, $first->status);
        self::assertSame(InvoiceStatus::VOID->value, $second->status);
        self::assertSame($combined->id, $first->consolidated_into_id);
        self::assertSame($combined->id, $second->consolidated_into_id);
    }

    /**
     * If the webhook mirrored the combined Stripe invoice first, save updates that row.
     */
    public function testItClaimsAWebhookMirroredCombinedInvoice(): void
    {
        [$studentId, $studentUserId] = $this->seedFamily();
        $this->insertOverdue($studentId, 'in_a', 'INV-A', -40, '2026-09');
        $this->insertOverdue($studentId, 'in_b', 'INV-B', -10, '2026-10');
        $mirroredId = DB::table('invoices')->insertGetId([
            'invoice_no' => 'in_combined',
            'student_id' => $studentId,
            'total_amount' => '20000.00',
            'due_date' => BillingSettings::now()
                ->addDays(BillingSettings::overdueGraceDays())
                ->toDateString(),
            'status' => InvoiceStatus::UNPAID->value,
            'stripe_invoice_id' => 'in_combined',
            'stripe_hosted_invoice_url' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mockMoneyGateway();
        $this->mockInvoiceOps(function (MockInterface $mock): void {
            $mock->shouldReceive('createConsolidatedDraft')
                ->andReturn($this->stripeInvoice('in_combined', 'draft', null));
            $mock->shouldReceive('retrieveInvoice')->andReturn($this->stripeInvoice('in_x', 'open'));
            $mock->shouldReceive('voidInvoice')->andReturn($this->stripeInvoice('in_x', 'void'));
            $mock->shouldReceive('finalizeInvoice')
                ->andReturn($this->stripeInvoice('in_combined', 'open', 'https://invoice.stripe.com/i/in_combined', 2000000));
        });

        app(PayOverdueInvoicesService::class)->handle(User::query()->findOrFail($studentUserId));

        self::assertSame(1, Invoice::query()->where('stripe_invoice_id', 'in_combined')->count());
        self::assertSame($mirroredId, (int) Invoice::query()->where('stripe_invoice_id', 'in_combined')->value('id'));
        self::assertSame(
            'https://invoice.stripe.com/i/in_combined',
            Invoice::query()->findOrFail($mirroredId)->stripe_hosted_invoice_url,
        );
    }

    /**
     * After consolidation, a second pay-overdue call finds no overdue invoices left.
     */
    public function testASecondRequestFindsNothingOverdueAfterConsolidation(): void
    {
        [$studentId, $studentUserId] = $this->seedFamily();
        $this->insertOverdue($studentId, 'in_a', 'INV-A', -40, '2026-09');
        $this->insertOverdue($studentId, 'in_b', 'INV-B', -10, '2026-10');

        $this->mockMoneyGateway();
        $this->mockInvoiceOps(function (MockInterface $mock): void {
            $mock->shouldReceive('createConsolidatedDraft')
                ->once()
                ->andReturn($this->stripeInvoice('in_combined', 'draft', null));
            $mock->shouldReceive('retrieveInvoice')->andReturn($this->stripeInvoice('in_x', 'open'));
            $mock->shouldReceive('voidInvoice')->twice()->andReturn($this->stripeInvoice('in_x', 'void'));
            $mock->shouldReceive('finalizeInvoice')
                ->once()
                ->andReturn($this->stripeInvoice('in_combined', 'open', 'https://invoice.stripe.com/i/in_combined', 2000000));
        });

        $service = app(PayOverdueInvoicesService::class);
        $user = User::query()->findOrFail($studentUserId);
        $service->handle($user);

        $this->expectException(ValidationException::class);
        $service->handle($user);
    }

    /**
     * Partial void failure re-issues already-voided originals with a future due date and deletes the draft.
     */
    public function testCompensationReissuesVoidedOriginalsAndDeletesTheDraft(): void
    {
        [$studentId, $studentUserId] = $this->seedFamily();
        $firstId = $this->insertOverdue($studentId, 'in_a', 'INV-A', -40, '2026-09');
        $this->insertOverdue($studentId, 'in_b', 'INV-B', -10, '2026-10');

        $expectedDue = BillingSettings::now()
            ->addDays(BillingSettings::overdueGraceDays())
            ->toDateString();
        $expectedDueAt = BillingSettings::atDueEndOfDay(
            BillingSettings::now()->addDays(BillingSettings::overdueGraceDays()),
        )->getTimestamp();

        $this->mock(StripeCheckoutGateway::class, function (MockInterface $mock) use ($expectedDueAt): void {
            $mock->shouldReceive('ensureStudentCustomer')->andReturn('cus_1');
            $mock->shouldReceive('toMinorUnits')->andReturn(1000000);
            $mock->shouldReceive('fromMinorUnits')->andReturn('10000.00');
            $mock->shouldReceive('createMonthlyInvoice')
                ->once()
                ->withArgs(function (
                    string $customerId,
                    int $amountMinor,
                    string $description,
                    array $metadata,
                    int $dueAt,
                    string $idempotencyKey,
                ) use ($expectedDueAt): bool {
                    // Stripe rejects past due dates; a reissue must not reuse the overdue date.
                    return $customerId === 'cus_1'
                        && $amountMinor === 1000000
                        && $dueAt === $expectedDueAt
                        && $dueAt > BillingSettings::now()->getTimestamp()
                        && str_contains($idempotencyKey, 'reissue-student-');
                })
                ->andReturn($this->stripeInvoice('in_a_reissue', 'open', 'https://invoice.stripe.com/i/in_a_reissue'));
        });
        $this->mockInvoiceOps(function (MockInterface $mock): void {
            $mock->shouldReceive('createConsolidatedDraft')
                ->andReturn($this->stripeInvoice('in_combined', 'draft', null));
            $mock->shouldReceive('retrieveInvoice')
                ->with('in_a')
                ->andReturn($this->stripeInvoice('in_a', 'open'));
            $mock->shouldReceive('retrieveInvoice')
                ->with('in_b')
                ->andReturn($this->stripeInvoice('in_b', 'open'));
            $mock->shouldReceive('voidInvoice')
                ->with('in_a')
                ->once()
                ->andReturn($this->stripeInvoice('in_a', 'void'));
            $mock->shouldReceive('voidInvoice')
                ->with('in_b')
                ->once()
                ->andThrow(new PaymentGatewayException('Stripe invoice in_b was paid and cannot be voided.'));
            $mock->shouldReceive('deleteDraftInvoice')->once()->with('in_combined');
            $mock->shouldReceive('finalizeInvoice')->never();
        });

        try {
            app(PayOverdueInvoicesService::class)->handle(User::query()->findOrFail($studentUserId));
            self::fail('Expected the paid original to abort consolidation.');
        } catch (PaymentGatewayException $error) {
            self::assertStringContainsString('cannot be voided', $error->getMessage());
        }

        $first = Invoice::query()->findOrFail($firstId);
        self::assertSame('in_a_reissue', $first->stripe_invoice_id);
        self::assertSame(InvoiceStatus::UNPAID->value, $first->status);
        self::assertSame($expectedDue, (string) $first->due_date);
        self::assertNull($first->consolidated_into_id);
        self::assertSame(0, Invoice::query()->where('stripe_invoice_id', 'in_combined')->count());
    }

    /**
     * Combined invoices without a billing period still receive remind-due notifications.
     */
    public function testTheCombinedInvoiceIsRemindedThreeDaysBeforeItsDueDate(): void
    {
        Notification::fake();
        [$studentId, $studentUserId, $parentUserId] = $this->seedFamily();
        DB::table('invoices')->insert([
            'invoice_no' => 'INV-COMBINED',
            'student_id' => $studentId,
            'total_amount' => '20000.00',
            'due_date' => BillingSettings::now()
                ->addDays(BillingSettings::reminderDaysBeforeDue())
                ->toDateString(),
            'status' => InvoiceStatus::UNPAID->value,
            'billing_period' => null,
            'stripe_invoice_id' => 'in_combined',
            'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/in_combined',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('invoices:remind-due')->assertSuccessful();

        Notification::assertSentTo(User::query()->findOrFail($studentUserId), InvoiceDueSoon::class);
        Notification::assertSentTo(User::query()->findOrFail($parentUserId), InvoiceDueSoon::class);
    }

    /**
     * A parent cannot pay overdue invoices for another parent's child.
     */
    public function testParentCannotPayOverdueForAnotherParentsChild(): void
    {
        [, $studentUserId] = $this->seedFamily();
        [, , $otherParentUserId] = $this->seedFamily();
        $this->insertOverdue(
            (int) Student::query()->where('user_id', $studentUserId)->value('id'),
            'in_a',
            'INV-A',
            -10,
        );

        Sanctum::actingAs(User::query()->findOrFail($otherParentUserId));

        $this->postJson('/api/invoices/pay-overdue', [
            'student_id' => (int) Student::query()->where('user_id', $studentUserId)->value('id'),
        ])->assertForbidden();
    }

    /**
     * Pay-overdue requires an authenticated Sanctum user.
     */
    public function testPayOverdueRequiresAuthentication(): void
    {
        $this->postJson('/api/invoices/pay-overdue')->assertUnauthorized();
    }

    /**
     * Stub customer and amount helpers on the Stripe checkout gateway.
     */
    private function mockMoneyGateway(): void
    {
        $this->mock(StripeCheckoutGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('ensureStudentCustomer')->andReturn('cus_1');
            $mock->shouldReceive('toMinorUnits')->andReturn(1000000);
            $mock->shouldReceive('fromMinorUnits')->andReturn('20000.00');
        });
    }

    /**
     * Register a StripeInvoiceOperations double with the given expectations.
     *
     * @param callable(\Mockery\MockInterface): void $configure Extra expectations.
     */
    private function mockInvoiceOps(callable $configure): void
    {
        $this->mock(StripeInvoiceOperations::class, function (MockInterface $mock) use ($configure): void {
            $configure($mock);
        });
    }

    /**
     * Build a Stripe invoice object as the SDK returns it.
     *
     * @param string $id The Stripe invoice ID.
     * @param string $status The Stripe status.
     * @param string|null $hostedUrl The hosted invoice URL, or null for drafts.
     * @param int $total The total in Stripe minor units.
     *
     * @return \Stripe\Invoice
     */
    private function stripeInvoice(
        string $id,
        string $status = 'open',
        ?string $hostedUrl = 'https://invoice.stripe.com/i/placeholder',
        int $total = 1000000,
    ): StripeInvoice {
        return StripeInvoice::constructFrom([
            'id' => $id,
            'number' => strtoupper($id),
            'status' => $status,
            'total' => $total,
            'hosted_invoice_url' => $hostedUrl,
        ]);
    }

    /**
     * Insert an unpaid invoice due a number of days from today in the billing timezone.
     *
     * @param int $studentId The student ID.
     * @param string $stripeId The Stripe invoice ID.
     * @param string $number The local invoice number.
     * @param int $daysFromToday Days offset from today (negative = overdue).
     * @param string|null $billingPeriod Optional YYYY-MM billing period.
     *
     * @return int The invoice id.
     */
    private function insertOverdue(
        int $studentId,
        string $stripeId,
        string $number,
        int $daysFromToday,
        ?string $billingPeriod = null,
    ): int {
        return DB::table('invoices')->insertGetId([
            'invoice_no' => $number,
            'student_id' => $studentId,
            'total_amount' => '10000.00',
            'due_date' => BillingSettings::now()->addDays($daysFromToday)->toDateString(),
            'status' => InvoiceStatus::UNPAID->value,
            'billing_period' => $billingPeriod,
            'stripe_invoice_id' => $stripeId,
            'stripe_hosted_invoice_url' => 'https://invoice.stripe.com/i/'.$stripeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
