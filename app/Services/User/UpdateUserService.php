<?php

namespace App\Services\User;

use App\Enum\UserRole;
use App\Interfaces\UserInterface;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\TeacherProfileUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateUserService
{
    /**
     * Repository interface for user data operations.
     *
     * @var \App\Interfaces\UserInterface
     */
    protected UserInterface $userInterface;

    /**
     * Initialize the service.
     *
     * @param \App\Interfaces\UserInterface $userInterface
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Update a user with the specified type.
     *
     * @param int $id The id of the student (students.id)
     * @param array<string, mixed> $data
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handleUpdateUser(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            /** @var User $user */
            $user = $this->updateUserData($data, $id);

            $this->updateUserTypeData($user, $data);

            DB::commit();

            return true;
        } catch (\Exception $error) {
            DB::rollBack();

            throw $error;
        }
    }

    /**
     * Update the base user record.
     *
     * @param array<string, mixed> $data
     * @param int $id The id of the student (students.id)
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function updateUserData(array $data, int $id): Model
    {
        $userData = [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'profile_image' => $data['profile_image'] ?? null,
            'is_active' => $data['status'],
        ];

        if (!empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }

        return $this->userInterface->update($userData, $id);
    }

    /**
     * Update the user-type specific record.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return mixed
     */
    protected function updateUserTypeData(User $user, array $data): mixed
    {
        $role = $data['role'];

        return match ($role) {
            UserRole::STUDENT->value => $this->updateStudentData($user, $data),
            UserRole::TEACHER->value => $this->updateTeacherData($user, $data),
            UserRole::PARENT->value => $this->updateParentData($user, $data),
            UserRole::ADMIN->value => null, // Admin has no additional table
            default => throw new \InvalidArgumentException("Invalid user role: {$role}"),
        };
    }

    /**
     * Update the student record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function updateStudentData(User $user, array $data): void
    {
        $user->student()->update([
            'user_id' => $user->id,
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'parent_id' => $data['parent_id'],
            'phone' => $data['phone'],
            'student_status' => (string) $data['status'],
            'address' => $data['address'],
        ]);
    }

    /**
     * Update the teacher record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function updateTeacherData(User $user, array $data): void
    {
        $teacher = $user->teacher;

        if (!$teacher instanceof Teacher) {
            throw new \Exception('Teacher profile not found for user.');
        }

        $teacher->update([
            'user_id' => $user->id,
            'employee_code' => $teacher->employee_code,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'specialization' => $data['specialization'],
            'experience_years' => $data['experience_years'],
        ]);

        $teacherChanges = collect($teacher->getChanges())
            ->except('updated_at')
            ->toArray();

        $userChanges = collect($user->getChanges())
            ->except(['updated_at', 'password'])
            ->toArray();

        $changes = array_merge($userChanges, $teacherChanges);

        /** @var array<int, array<string, mixed>> $classes */
        $classes = $data['classes'] ?? [];

        // Add/Update teacher class assignment
        $ids = collect($classes)
            ->map(function (array $class) use ($teacher): int {
                $assignment = $teacher->teacherClassAssignments()
                    ->updateOrCreate(
                        ['id' => $class['id'] ?? null],
                        [
                            'class_id' => $class['class_id'],
                            'section_id' => $class['section_id'],
                            'academic_year_id' => $class['academic_year_id'],
                        ],
                    );

                return (int) $assignment->getKey();
            })
            ->all();

        $teacher->teacherClassAssignments()
            ->whereNotIn('id', $ids)
            ->delete();

        // Send notification to the teacher only after the outer transaction
        // successfully commits. This prevents the 'database' row and the
        // real-time 'broadcast' event from firing if handleUpdateUser()
        // later rolls back due to an error elsewhere in the transaction.
        DB::afterCommit(function () use ($user, $changes) {
            $user->notify(new TeacherProfileUpdated($changes));
        });
    }

    /**
     * Update the parent record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function updateParentData(User $user, array $data): void
    {
        $user->parent()->update([

        ]);
    }
}
