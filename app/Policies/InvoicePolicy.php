<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\{
    Invoice,
    Parents,
    Student,
    User,
};

class InvoicePolicy
{
    /**
     * Determine whether the user may start a Checkout session for an invoice.
     *
     * @param \App\Models\User $user The authenticated payer.
     * @param \App\Models\Invoice $invoice The invoice being paid.
     *
     * @return bool
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        return match ($user->role) {
            UserRole::STUDENT->value => $this->isStudentInvoice($user, $invoice),
            UserRole::PARENT->value => $this->isParentInvoice($user, $invoice),
            default => false,
        };
    }

    /**
     * Determine whether the invoice belongs to the student's profile.
     *
     * @param \App\Models\User $user The authenticated student user.
     * @param \App\Models\Invoice $invoice The invoice being paid.
     *
     * @return bool
     */
    private function isStudentInvoice(User $user, Invoice $invoice): bool
    {
        /** @var Student|null $student */
        $student = $user->student;
        $studentId = $student?->id;

        return $studentId !== null && (int) $studentId === $invoice->student_id;
    }

    /**
     * Determine whether the invoice belongs to a student linked to the parent.
     *
     * @param \App\Models\User $user The authenticated parent user.
     * @param \App\Models\Invoice $invoice The invoice being paid.
     *
     * @return bool
     */
    private function isParentInvoice(User $user, Invoice $invoice): bool
    {
        /** @var Parents|null $parent */
        $parent = $user->parent;
        $parentId = $parent?->id;
        /** @var Student|null $student */
        $student = $invoice->student;

        return $parentId !== null
            && $student?->parent_id === $parentId;
    }
}
