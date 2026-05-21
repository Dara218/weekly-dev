<?php

namespace App\Services\Teacher;

use App\Interfaces\TeacherInterface;

class GetTeacherService
{
    /**
     * Repository interface for teacher data operations.
     *
     * @var \App\Interfaces\TeacherInterface
     */
    protected TeacherInterface $teacherInterface;

    /**
     * Initialize the service.
     *
     * @param \App\Interfaces\TeacherInterface $teacherInterface
     */
    public function __construct(TeacherInterface $teacherInterface)
    {
        $this->teacherInterface = $teacherInterface;
    }

    /**
     * Get teachers filtered by the given search keywords.
     *
     * @param array<mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeachers(array $keywords)
    {
        return $this->teacherInterface->getTeachersBySearch($keywords);
    }
}
