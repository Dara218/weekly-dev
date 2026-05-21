<?php

namespace App\Repositories;

use App\Interfaces\TeacherInterface;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository extends BaseRepository implements TeacherInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Teacher $model
     */
    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getTeachersBySearch(array $keywords): Collection
    {
        return Teacher::query()
            ->search($keywords)
            ->get();
    }
}
