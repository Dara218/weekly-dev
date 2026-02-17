<?php

namespace App\Repositories;

use App\Interfaces\ParentInterface;
use App\Models\Parents;

class ParentRepository extends BaseRepository implements ParentInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\Parents $model
     */
    public function __construct(Parents $model)
    {
        parent::__construct($model);
    }
}
