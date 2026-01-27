<?php

namespace App\Repositories;

use App\Interfaces\StudentInterface;
use App\Models\Student;

class StudentRepository extends BaseRepository implements StudentInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Student $model
     */
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    /**
     * Get students filtered by the given search keywords.
     *
     * @param array<mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentsBySearch(array $keywords)
    {
        return Student::query()->search($keywords)->get();
    }
}
