<?php

namespace Tests\Unit\Services\Payment;

use App\Enum\UserRole;
use App\Models\{
    Parents,
    Student,
    User,
};
use App\Services\Payment\GetParentChildrenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Unit tests for parent child listing and authorization in GetParentChildrenService.
 */
class GetParentChildrenServiceTest extends TestCase
{
    /**
     * A parent with no linked students receives an empty list.
     */
    public function testItReturnsAnEmptyListWhenTheParentHasNoChildren(): void
    {
        $payer = $this->parentWith([]);

        self::assertSame([], (new GetParentChildrenService())->handle($payer));
    }

    /**
     * Linked children are returned sorted by student ID with name and status fields.
     */
    public function testItReturnsEachLinkedChildSortedById(): void
    {
        $payer = $this->parentWith([
            $this->student(9, 'Ben', 'Two', '1'),
            $this->student(4, 'Ana', 'One', '1'),
            $this->student(12, 'Cy', 'Three', '0'),
        ]);

        $result = (new GetParentChildrenService())->handle($payer);

        self::assertSame(
            [
                ['id' => 4, 'name' => 'Ana One', 'student_status' => '1'],
                ['id' => 9, 'name' => 'Ben Two', 'student_status' => '1'],
                ['id' => 12, 'name' => 'Cy Three', 'student_status' => '0'],
            ],
            $result,
        );
    }

    /**
     * A child without a user profile yields a null display name.
     */
    public function testItReturnsNullNameWhenAChildHasNoUserProfile(): void
    {
        $student = new Student();
        $student->forceFill(['id' => 5, 'student_status' => '1']);
        $student->setRelation('user', null);

        $result = (new GetParentChildrenService())->handle($this->parentWith([$student]));

        self::assertNull($result[0]['name']);
    }

    /**
     * Non-parent users are rejected when listing children.
     */
    public function testItRejectsUsersThatAreNotParents(): void
    {
        $user = new User();
        $user->forceFill(['role' => UserRole::STUDENT->value]);

        $this->expectException(AuthorizationException::class);

        (new GetParentChildrenService())->handle($user);
    }

    /**
     * Parent-role users without a parent profile are rejected.
     */
    public function testItRejectsParentUsersWithoutAParentProfile(): void
    {
        $user = new User();
        $user->forceFill(['role' => UserRole::PARENT->value]);
        $user->setRelation('parent', null);

        $this->expectException(AuthorizationException::class);

        (new GetParentChildrenService())->handle($user);
    }

    /**
     * Build a parent user with the given students already loaded.
     *
     * @param list<\App\Models\Student> $students The linked students.
     *
     * @return \App\Models\User
     */
    private function parentWith(array $students): User
    {
        $parent = new Parents();
        $parent->forceFill(['id' => 3]);
        $parent->setRelation('students', new Collection($students));

        $payer = new User();
        $payer->forceFill(['role' => UserRole::PARENT->value]);
        $payer->setRelation('parent', $parent);

        return $payer;
    }

    /**
     * Build a student with a loaded user profile.
     *
     * @param int $id The student ID.
     * @param string $first The first name.
     * @param string $last The last name.
     * @param string $status The raw student status.
     *
     * @return \App\Models\Student
     */
    private function student(int $id, string $first, string $last, string $status): Student
    {
        $user = new User();
        $user->forceFill([
            'first_name' => $first,
            'middle_name' => null,
            'last_name' => $last,
        ]);

        $student = new Student();
        $student->forceFill([
            'id' => $id,
            'student_status' => $status,
        ]);
        $student->setRelation('user', $user);

        return $student;
    }
}