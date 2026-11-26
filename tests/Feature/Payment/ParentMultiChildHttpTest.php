<?php

namespace Tests\Feature\Payment;

use App\Enum\{
    InvoiceStatus,
    UserRole,
};
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RequiresPhpunitMysql;
use Tests\TestCase;

/**
 * HTTP tests for parent children listing and invoice filtering across multiple children.
 *
 * Runs only against a dedicated MySQL database whose name ends in _phpunit.
 * Uses DatabaseTransactions so each test rolls back instead of refreshing the schema.
 */
class ParentMultiChildHttpTest extends TestCase
{
    use RequiresPhpunitMysql;

    /**
     * Seeded fixture IDs keyed by role (parentUser, childA, foreign, …).
     *
     * @var array<string, int>
     */
    private array $ids = [];

    /**
     * Skip unless the dedicated phpunit MySQL database is configured, then seed fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRequiresPhpunitMysql();

        $this->ids['parentUser'] = $this->insertUser('PARENT', 'p1@test.local', 'Pat');
        $this->ids['parent'] = $this->insertParent($this->ids['parentUser']);
        $this->ids['otherParentUser'] = $this->insertUser('PARENT', 'p2@test.local', 'Olga');
        $this->ids['otherParent'] = $this->insertParent($this->ids['otherParentUser']);

        $this->ids['childA'] = $this->insertStudent('a@test.local', 'Ana', $this->ids['parent'], 'sub_a', 'active');
        $this->ids['childB'] = $this->insertStudent('b@test.local', 'Ben', $this->ids['parent'], null, null);
        $this->ids['foreign'] = $this->insertStudent('c@test.local', 'Cy', $this->ids['otherParent'], null, null);

        $this->insertInvoice('INV-A1', $this->ids['childA']);
        $this->insertInvoice('INV-A2', $this->ids['childA']);
        $this->insertInvoice('INV-B1', $this->ids['childB']);
        $this->insertInvoice('INV-C1', $this->ids['foreign']);
    }

    /**
     * Parent children lists only linked students, with names and without subscription flags.
     */
    public function testParentChildrenListsOnlyOwnChildren(): void
    {
        $this->actAs($this->ids['parentUser']);

        $response = $this->getJson('/api/parent/children');

        $response->assertOk()->assertJsonPath('success', true);
        $data = $response->json('data');

        self::assertSame([$this->ids['childA'], $this->ids['childB']], array_column($data, 'id'));
        self::assertSame(['Ana Mid Test', 'Ben Mid Test'], array_column($data, 'name'));
        self::assertArrayNotHasKey('subscribed', $data[0]);
        self::assertContains($data[0]['student_status'], ['1', 1]);
    }

    /**
     * Non-parent users cannot list parent children.
     */
    public function testParentChildrenIsForbiddenForNonParents(): void
    {
        $studentUserId = (int) DB::table('students')->where('id', $this->ids['childA'])->value('user_id');
        $this->actAs($studentUserId);

        $this->getJson('/api/parent/children')->assertForbidden();
    }

    /**
     * Parent children requires Sanctum authentication.
     */
    public function testParentChildrenRequiresAuthentication(): void
    {
        $this->getJson('/api/parent/children')->assertUnauthorized();
    }

    /**
     * Unfiltered invoices return all of the parent's children and never another parent's.
     */
    public function testInvoicesWithoutFilterReturnAllOwnChildrenButNotOthers(): void
    {
        $this->actAs($this->ids['parentUser']);

        $numbers = array_column($this->getJson('/api/invoices')->assertOk()->json('data'), 'invoice_no');
        sort($numbers);

        self::assertSame(['INV-A1', 'INV-A2', 'INV-B1'], $numbers);
    }

    /**
     * student_id filter returns only invoices for that linked child.
     */
    public function testInvoicesCanBeFilteredToOneOwnedChild(): void
    {
        $this->actAs($this->ids['parentUser']);

        $response = $this->getJson('/api/invoices?student_id=' . $this->ids['childB'])->assertOk();

        self::assertSame(['INV-B1'], array_column($response->json('data'), 'invoice_no'));
        self::assertSame(1, $response->json('meta.total'));
    }

    /**
     * Filtering by another parent's student is forbidden.
     */
    public function testInvoicesFilterRejectsAStudentOfAnotherParent(): void
    {
        $this->actAs($this->ids['parentUser']);

        $this->getJson('/api/invoices?student_id=' . $this->ids['foreign'])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    /**
     * Authenticate as the given user via Sanctum.
     *
     * @param int $userId The user ID.
     */
    private function actAs(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        self::assertContains($user->role, [UserRole::PARENT->value, UserRole::STUDENT->value]);
        Sanctum::actingAs($user);
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

    /**
     * Insert a parents row linked to a user.
     *
     * @param int $userId The parent user ID.
     *
     * @return int The parent ID.
     */
    private function insertParent(int $userId): int
    {
        return DB::table('parents')->insertGetId([
            'user_id' => $userId,
            'phone' => '123',
            'occupation' => 'Job',
            'address' => 'Somewhere',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Insert a student user and students row under a parent.
     *
     * @param string $email The student email.
     * @param string $first The first name.
     * @param int $parentId The linked parent ID.
     * @param string|null $subscriptionId Optional legacy Stripe subscription ID.
     * @param string|null $subscriptionStatus Optional legacy subscription status.
     *
     * @return int The student ID.
     */
    private function insertStudent(
        string $email,
        string $first,
        int $parentId,
        ?string $subscriptionId,
        ?string $subscriptionStatus,
    ): int {
        $userId = $this->insertUser('STUDENT', $email, $first);

        return DB::table('students')->insertGetId([
            'user_id' => $userId,
            'parent_id' => $parentId,
            'admission_no' => 'ADM-' . $userId,
            'class_id' => 1,
            'section_id' => 1,
            'gender' => 'MALE',
            'dob' => '2010-05-15',
            'address' => 'Somewhere',
            'student_status' => '1',
            'stripe_subscription_id' => $subscriptionId,
            'stripe_subscription_status' => $subscriptionStatus,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Insert an unpaid invoice for a student.
     *
     * @param string $number The invoice number.
     * @param int $studentId The student ID.
     */
    private function insertInvoice(string $number, int $studentId): void
    {
        DB::table('invoices')->insert([
            'invoice_no' => $number,
            'student_id' => $studentId,
            'total_amount' => '10000.00',
            'due_date' => '2026-12-01',
            'status' => InvoiceStatus::UNPAID->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
