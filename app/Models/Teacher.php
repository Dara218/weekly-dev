<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Get the user associated with the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sections assigned to the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Get the subjects taught by the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get the timetables for the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeTables()
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * Get the attendance records marked by the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'marked_by');
    }

    /**
     * Get the class assignments for the teacher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teacherClassAssignments()
    {
        return $this->hasMany(TeacherClassAssignment::class);
    }
}
