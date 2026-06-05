<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Builder,
    Factories\HasFactory,
    Model,
    SoftDeletes,
};

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_code',
        'phone',
        'address',
        'specialization',
        'experience_years'
    ];

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

    /**
     * Scope a query to filter teachers by the given keywords.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $keywords
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, array $keywords): Builder
    {
        return $query
            ->when(!empty($keywords['subject_id']), function ($query) use ($keywords) {
                $query->whereHas('subjects', function ($q) use ($keywords) {
                    $q->where('id', $keywords['subject_id']);
                });
            })
            ->when(array_key_exists('status', $keywords), function ($query) use ($keywords) {
                $query->whereHas('user', function ($q) use ($keywords) {
                    $q->where('is_active', $keywords['status']);
                });
            })
            ->when(!empty($keywords['search']), function ($query) use ($keywords) {
                $search = $keywords['search'];

                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q2) use ($search) {
                        $q2->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('middle_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"]);
                    })
                    ->orWhere('employee_code', 'LIKE', "%{$search}%");
                });
            })
            ->with('user', 'teacherClassAssignments');
    }
}
