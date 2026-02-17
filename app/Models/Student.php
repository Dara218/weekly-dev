<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Builder,
    Factories\HasFactory,
    Model,
    SoftDeletes,
};

class Student extends Model
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
        'parent_id',
        'admission_no',
        'class_id',
        'section_id',
        'gender',
        'dob',
        'address',
        'student_status',
        'phone',
    ];

    /**
     * Get the user associated with the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent associated with the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    /**
     * Get the class associated with the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    /**
     * Get the section associated with the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the attendance details for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceDetails()
    {
        return $this->hasMany(AttendanceDetail::class);
    }

    /**
     * Get the grades for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the invoices for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the issued books for the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class);
    }

    /**
     * Scope a query to filter students by the given keywords.
     *
     * @param Builder $query
     * @param array<string, mixed> $keywords
     *
     * @return Builder
     */
    public function scopeSearch(Builder $query, array $keywords)
    {
        // Check if any other filters are active (excluding keyword)
        // 0 is default value in front end options. 3 is default value for status options
        $hasOtherFilters = ($keywords['class'] != 0)
            || ($keywords['section'] != 0)
            || ($keywords['gender'] != 0)
            || ($keywords['status'] != 3)
            || ($keywords['admission_year'] != 0);

        return $query
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->join('academic_years', 'classes.academic_year_id', '=', 'academic_years.id')
            ->when($keywords['class'] != 0, function ($query) use ($keywords) {
                $query->where('students.class_id', $keywords['class']);
            })
            ->when($keywords['section'] != 0, function ($query) use ($keywords) {
                $query->where('students.section_id', $keywords['section']);
            })
            ->when($keywords['gender'] != 0, function ($query) use ($keywords) {
                $query->where('students.gender', $keywords['gender']);
            })
            ->when($keywords['status'] != 3, function ($query) use ($keywords) {
                $query->where('students.student_status', $keywords['status']);
            })
            ->when($keywords['admission_year'] != 0, function ($query) use ($keywords) {
                $query->where('academic_years.name', $keywords['admission_year']);
            })
            ->when(
                array_key_exists('name_or_admission_number_keyword', $keywords),
                function ($query) use ($keywords, $hasOtherFilters) {
                    $keyword = trim($keywords['name_or_admission_number_keyword'] ?? '');

                    // If keyword is empty and no other filters are set, return no results
                    if ($keyword === '' && !$hasOtherFilters) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    // If keyword has value, filter by it
                    if ($keyword !== '') {
                        $query->where(function ($query) use ($keyword) {
                            $query->where('users.name', 'LIKE', "%{$keyword}%")
                                ->orWhere('students.admission_no', 'LIKE', "%{$keyword}%");
                        });
                    }
                    // If keyword is empty but other filters exist, skip keyword filter (allow other filters to work)
                }
            )
            ->with(
                'user',
                'class',
                'parent.user',
                'section',
            );
    }
}
