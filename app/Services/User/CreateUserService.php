<?php

namespace App\Services\User;

use App\Enum\{
    StudentSectionEnrollmentStatus,
    UserRole,
};
use App\Interfaces\{
    ClassesInterface,
    UserInterface,
};
use App\Models\{
    Student,
    User,
};
use App\Services\Common\AdmissionNumberService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUserService
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
     * Create a user with the specified type.
     *
     * @param array<string, mixed> $data
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handleCreateUser(array $data): bool
    {
        DB::beginTransaction();

        try {
            /** @var User $user */
            $user = $this->createUserData($data);

            $this->createUserTypeData($user, $data);

            DB::commit();

            return true;
        } catch (\Exception $error) {
            DB::rollBack();

            throw $error;
        }
    }

    /**
     * Create the base user record.
     *
     * @param array<string, mixed> $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createUserData(array $data): Model
    {
        return $this->userInterface->create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'profile_image' => $data['profile_image'] ?? null,
            'is_active' => $data['status'],
        ]);
    }

    /**
     * Create the user-type specific record.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return mixed
     */
    protected function createUserTypeData(User $user, array $data): mixed
    {
        $role = $data['role'];

        return match ($role) {
            UserRole::STUDENT->value => $this->createStudentData($user, $data),
            UserRole::TEACHER->value => $this->createTeacherData($user, $data),
            UserRole::PARENT->value => $this->createParentData($user, $data),
            UserRole::ADMIN->value => null, // Admin has no additional table
            default => throw new \InvalidArgumentException("Invalid user role: {$role}"),
        };
    }

    /**
     * Create the student record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function createStudentData(User $user, array $data): void
    {
        $admissionYearId = app(ClassesInterface::class)
            ->find($data['class_id'])
            ->academic_year_id;

        /** @var Student $student */
        $student = $user->student()->create([
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'admission_no' => app(AdmissionNumberService::class)->generate($admissionYearId),
            'parent_id' => $data['parent_id'],
            'phone' => $data['phone'],
            'student_status' => $data['status'],
            'address' => $data['address'],
        ]);

        // Create enrollment data
        $this->createSectionEnrollmentData($student, $admissionYearId);
    }

    /**
     * Create the section enrollment data for the student.
     *
     * @param Student $student
     * @param int $admissionYearId
     *
     * @return void
     */
    protected function createSectionEnrollmentData(Student $student, int $admissionYearId): void
    {
        // Create enrollment data
        $student->sectionEnrollments()->create([
            'section_id' => $student->section_id,
            'academic_year_id' => $admissionYearId,
            'status' => StudentSectionEnrollmentStatus::PENDING->value,
        ]);
    }

    /**
     * Create the teacher record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function createTeacherData(User $user, array $data): void
    {
        $user->teacher()->create([

        ]);
    }

    /**
     * Create the parent record for the user.
     *
     * @param \App\Models\User $user
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function createParentData(User $user, array $data): void
    {
        $user->parent()->create([

        ]);
    }
}
