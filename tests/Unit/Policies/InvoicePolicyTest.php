<?php

namespace Tests\Unit\Policies;

use App\Enum\UserRole;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};
use App\Policies\InvoicePolicy;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for who may pay an invoice through InvoicePolicy.
 */
class InvoicePolicyTest extends TestCase
{
    /**
     * Administrators cannot use the online invoice payment flow.
     */
    public function testAdministratorCannotUseTheOnlinePaymentFlow(): void
    {
        $user = new User();
        $user->forceFill(['role' => UserRole::ADMIN->value]);

        self::assertFalse((new InvoicePolicy())->pay($user, new Invoice()));
    }

    /**
     * Parents may pay only for invoices belonging to their linked students.
     */
    public function testParentCanOnlyPayForALinkedStudent(): void
    {
        $parent = new Parents();
        $parent->forceFill(['id' => 4]);

        $user = new User();
        $user->forceFill(['role' => UserRole::PARENT->value]);
        $user->setRelation('parent', $parent);

        $linkedStudent = new Student();
        $linkedStudent->forceFill(['parent_id' => 4]);
        $linkedInvoice = new Invoice();
        $linkedInvoice->setRelation('student', $linkedStudent);

        $otherStudent = new Student();
        $otherStudent->forceFill(['parent_id' => 9]);
        $otherInvoice = new Invoice();
        $otherInvoice->setRelation('student', $otherStudent);

        $policy = new InvoicePolicy();

        self::assertTrue($policy->pay($user, $linkedInvoice));
        self::assertFalse($policy->pay($user, $otherInvoice));
    }
}