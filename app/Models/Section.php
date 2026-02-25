<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany,
    HasMany,
};

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * Get the students in this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Enrollment rows for this section (student_section_enrollments).
     */
    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentSectionEnrollment::class);
    }

    /**
     * Students enrolled in this section via student_section_enrollments.
     *
     * Pivot columns: academic_year_id, status
     *
     * @return BelongsToMany<Student, $this, \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'>
     */
    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_section_enrollments')
            ->withPivot(['academic_year_id', 'status']) // Include columns
            ->withTimestamps();
    }

    /**
     * Get the class that this section belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class);
    }

    /**
     * Get the teacher assigned to this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the timetables for this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeTables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * Get the attendance records for this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
