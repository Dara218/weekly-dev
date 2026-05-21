<?php

namespace App\Repositories;

use App\Interfaces\StudentInterface;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

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
     * {@inheritDoc}
     */
    public function getStudentsBySearch(array $keywords): Collection
    {
        return Student::query()
            ->search($keywords)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStudentCountByCurrentYear(int $academicYearId): int
    {
        return $this->model->whereHas('class', function ($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })->count() + 1;
    }
}
