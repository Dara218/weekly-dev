<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    /**
     * Get the exam subjects for this exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }
}
