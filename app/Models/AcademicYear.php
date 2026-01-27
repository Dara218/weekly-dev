<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * Get the class associated with this academic year.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function class()
    {
        return $this->hasOne(Classes::class);
    }
}
