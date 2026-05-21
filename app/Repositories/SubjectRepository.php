<?php

namespace App\Repositories;

use App\Interfaces\SubjectInterface;
use App\Models\Subject;

class SubjectRepository extends BaseRepository implements SubjectInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Subject $model
     */
    public function __construct(Subject $model)
    {
        parent::__construct($model);
    }
}
