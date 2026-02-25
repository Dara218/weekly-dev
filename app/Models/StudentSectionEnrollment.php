<?php

namespace App\Models;

use App\Enum\StudentSectionEnrollmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSectionEnrollment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => StudentSectionEnrollmentStatus::class,
    ];

    /**
     * Get the student for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the section for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the academic year for this enrollment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
