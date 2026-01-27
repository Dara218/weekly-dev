<?php

namespace App\Repositories;

use App\Enum\UserRole;
use App\Interfaces\UserInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get the user profile with relationships based on user role.
     *
     * @param int $userId
     *
     * @return \App\Models\User
     */
    public function getRoleBasedProfile(int $userId)
    {
        $user = $this->find($userId);

        $relationship = match ($user->role) {
            UserRole::TEACHER->value => [
                'teacher.subjects',
                'teacher.timeTables',
                'teacher.teacherClassAssignments',
                'teacher.teacherClassAssignments.academicYear',
                'teacher.teacherClassAssignments.class',
                'teacher.teacherClassAssignments.section',
            ],
            UserRole::STUDENT->value => [
                'student.class',
                'student.section',
                'student.section.teacher',
                'student.parent',
            ],
            UserRole::PARENT->value => ['parent.students'],
            UserRole::ADMIN->value => [],
            default => [],
        };

        return $user->load($relationship);
    }
}
