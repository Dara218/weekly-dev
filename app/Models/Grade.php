<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    /**
     * Get the exam subject associated with this grade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class);
    }

    /**
     * Get the student associated with this grade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
