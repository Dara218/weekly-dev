<?php

namespace App\Services\Student;

use App\Interfaces\StudentInterface;

class StudentService
{
    /**
     * Repository interface for student data operations.
     *
     * @var \App\Interfaces\StudentInterface
     */
    protected StudentInterface $studentInterface;

    /**
     * Initialize the service.
     *
     * @param \App\Interfaces\StudentInterface $studentInterface
     */
    public function __construct(StudentInterface $studentInterface)
    {
        $this->studentInterface = $studentInterface;
    }

    /**
     * Get students filtered by the given search keywords.
     *
     * @param array<mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudents(array $keywords)
    {
        return $this->studentInterface->getStudentsBySearch($keywords);
    }
}
