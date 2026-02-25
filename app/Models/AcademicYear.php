<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    HasMany,
    HasOne,
};

class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * Get the class associated with this academic year.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function class(): HasOne
    {
        return $this->hasOne(Classes::class);
    }

    /**
     * Classes for this academic year.
     *
     * (Matches DB schema: classes.academic_year_id -> academic_years.id)
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Classes::class);
    }

    /**
     * Student-section enrollments for this academic year.
     */
    public function studentSectionEnrollments(): HasMany
    {
        return $this->hasMany(StudentSectionEnrollment::class);
    }
}
