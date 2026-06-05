<?php

namespace App\Repositories;

use App\Interfaces\SectionInterface;
use App\Models\Section;

class SectionRepository extends BaseRepository implements SectionInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Section $model
     */
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }
}
