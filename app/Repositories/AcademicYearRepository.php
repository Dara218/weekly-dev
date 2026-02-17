<?php

namespace App\Repositories;

use App\Interfaces\AcademicYearInterface;
use App\Models\AcademicYear;

class AcademicYearRepository extends BaseRepository implements AcademicYearInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\AcademicYear $model
     */
    public function __construct(AcademicYear $model)
    {
        parent::__construct($model);
    }
}
