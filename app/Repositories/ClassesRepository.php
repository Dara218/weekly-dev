<?php

namespace App\Repositories;

use App\Interfaces\ClassesInterface;
use App\Models\Classes;

class ClassesRepository extends BaseRepository implements ClassesInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Classes $model
     */
    public function __construct(Classes $model)
    {
        parent::__construct($model);
    }
}
