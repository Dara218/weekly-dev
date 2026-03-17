<?php

namespace App\Services\User;

use App\Enum\UserRole;
use App\Interfaces\UserInterface;
use App\Models\User;
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
        $user->teacher()->update([

        ]);
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
