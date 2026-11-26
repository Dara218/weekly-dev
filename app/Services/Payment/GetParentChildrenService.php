<?php

namespace App\Services\Payment;

use App\Enum\UserRole;
use App\Models\{
    Parents,
    Student,
    User,
};
use Illuminate\Auth\Access\AuthorizationException;

class GetParentChildrenService
{
    /**
     * Return the students linked to the authenticated parent.
     *
     * @param \App\Models\User $user The authenticated parent user.
     *
     * @return list<array{id: int, name: string|null, student_status: string|null}>
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user): array
    {
        if ($user->role !== UserRole::PARENT->value) {
            throw new AuthorizationException('Only parents may list their children.');
        }

        /** @var Parents|null $parent */
        $parent = $user->parent;

        if ($parent === null) {
            throw new AuthorizationException('No parent profile is linked to this account.');
        }

        $parent->loadMissing('students.user');

        $children = [];

        /** @var Student $student */
        foreach ($parent->students->sortBy('id') as $student) {
            $children[] = [
                'id' => (int) $student->id,
                'name' => $this->studentName($student),
                'student_status' => is_string($student->student_status)
                    ? $student->student_status
                    : null,
            ];
        }

        return $children;
    }

    /**
     * Build a display name from the student's user profile.
     *
     * @param \App\Models\Student $student The student.
     *
     * @return string|null
     */
    private function studentName(Student $student): ?string
    {
        /** @var User|null $studentUser */
        $studentUser = $student->user;

        if ($studentUser === null) {
            return null;
        }

        return implode(' ', array_filter([
            $studentUser->first_name,
            $studentUser->middle_name,
            $studentUser->last_name,
        ]));
    }
}
