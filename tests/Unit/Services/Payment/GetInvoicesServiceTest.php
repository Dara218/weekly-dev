<?php

namespace Tests\Unit\Services\Payment;

use App\Enum\{
    InvoiceStatus,
    UserRole,
};
use App\Interfaces\InvoiceInterface;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use App\Services\Payment\GetInvoicesService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Unit tests for invoice listing, role-based access, and response shaping in GetInvoicesService.
 */
class GetInvoicesServiceTest extends TestCase
{
    /**
     * Student payers receive paginated invoices with outstanding balances and pay flags.
     */
    public function testItReturnsStudentInvoicesWithCalculatedBalances(): void
    {
        $studentUser = new User();
        $studentUser->forceFill([
            'first_name' => 'Ana',
            'middle_name' => 'Maria',
            'last_name' => 'Student',
        ]);

        $student = new Student();
        $student->forceFill(['id' => 7]);
        $student->setRelation('user', $studentUser);

        $payer = new User();
        $payer->forceFill(['role' => UserRole::STUDENT->value]);
        $payer->setRelation('student', $student);

        $invoice = new Invoice();
        $invoice->forceFill([
            'id' => 15,
            'invoice_no' => 'INV-100',
            'student_id' => 7,
            'total_amount' => '1000.00',
            'status' => InvoiceStatus::PARTIAL->value,
            'due_date' => '2026-10-31',
            'amount_paid' => '400.00',
        ]);
        $invoice->setRelation('student', $student);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::once())
            ->method('listForStudent')
            ->with(7, 20)
            ->willReturn(new LengthAwarePaginator([$invoice], 1, 20, 1));
        $repository->expects(self::never())->method('listForParent');

        $result = (new GetInvoicesService($repository))->handle($payer, 20);

        self::assertSame(
            [
                'data' => [
                    [
                        'id' => 15,
                        'invoice_no' => 'INV-100',
                        'student_id' => 7,
                        'student_name' => 'Ana Maria Student',
                        'total_amount' => '1000.00',
                        'amount_paid' => '400.00',
                        'outstanding' => '600.00',
                        'status' => InvoiceStatus::PARTIAL->value,
                        'due_date' => '2026-10-31',
                        'stripe_invoice_id' => null,
                        'stripe_hosted_invoice_url' => null,
                        'can_pay' => false,
                        'is_overdue' => false,
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 1,
                ],
            ],
            $result,
        );
    }

    /**
     * Parents filtering by a linked student ID list only that child's invoices.
     */
    public function testParentWithAnOwnedStudentIdListsOnlyThatStudentsInvoices(): void
    {
        $payer = $this->parentWithChildren([7, 8]);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::once())
            ->method('listForStudent')
            ->with(8, 20)
            ->willReturn(new LengthAwarePaginator([], 0, 20, 1));
        $repository->expects(self::never())->method('listForParent');

        $result = (new GetInvoicesService($repository))->handle($payer, 20, 8);

        self::assertSame([], $result['data']);
    }

    /**
     * Parents without a student filter aggregate invoices for all linked children.
     */
    public function testParentWithoutAStudentIdStillListsAllLinkedChildrenInvoices(): void
    {
        $payer = $this->parentWithChildren([7, 8]);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::once())
            ->method('listForParent')
            ->with(3, 20)
            ->willReturn(new LengthAwarePaginator([], 0, 20, 1));
        $repository->expects(self::never())->method('listForStudent');

        (new GetInvoicesService($repository))->handle($payer, 20);
    }

    /**
     * Parents cannot filter invoices by a student that is not linked to them.
     */
    public function testParentCannotFilterByAStudentThatIsNotLinked(): void
    {
        $payer = $this->parentWithChildren([7, 8]);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::never())->method('listForStudent');
        $repository->expects(self::never())->method('listForParent');

        $this->expectException(AuthorizationException::class);

        (new GetInvoicesService($repository))->handle($payer, 20, 99);
    }

    /**
     * Student payers ignore an optional student ID filter and always list their own invoices.
     */
    public function testStudentIgnoresTheStudentIdFilter(): void
    {
        $student = new Student();
        $student->forceFill(['id' => 7]);

        $payer = new User();
        $payer->forceFill(['role' => UserRole::STUDENT->value]);
        $payer->setRelation('student', $student);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::once())
            ->method('listForStudent')
            ->with(7, 20)
            ->willReturn(new LengthAwarePaginator([], 0, 20, 1));

        (new GetInvoicesService($repository))->handle($payer, 20, 99);
    }

    /**
     * Requested page size is capped at the configured maximum before querying.
     */
    public function testItCapsTheRequestedPageSizeAtTheConfiguredMaximum(): void
    {
        $student = new Student();
        $student->forceFill(['id' => 7]);

        $payer = new User();
        $payer->forceFill(['role' => UserRole::STUDENT->value]);
        $payer->setRelation('student', $student);

        $repository = $this->createMock(InvoiceInterface::class);
        $repository->expects(self::once())
            ->method('listForStudent')
            ->with(7, 50)
            ->willReturn(new LengthAwarePaginator([], 0, 50, 1));

        $result = (new GetInvoicesService($repository))->handle($payer, 500);

        self::assertSame(50, $result['meta']['per_page']);
        self::assertSame([], $result['data']);
    }

    /**
     * Build a parent user whose linked students are already loaded.
     *
     * @param list<int> $studentIds The linked student IDs.
     *
     * @return \App\Models\User
     */
    private function parentWithChildren(array $studentIds): User
    {
        $students = [];

        foreach ($studentIds as $studentId) {
            $student = new Student();
            $student->forceFill(['id' => $studentId]);
            $students[] = $student;
        }

        $parent = new Parents();
        $parent->forceFill(['id' => 3]);
        $parent->setRelation('students', new Collection($students));

        $payer = new User();
        $payer->forceFill(['role' => UserRole::PARENT->value]);
        $payer->setRelation('parent', $parent);

        return $payer;
    }
}