<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /**
     * Get the class that this subject belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    /**
     * Get the teacher for this subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the timetables for this subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeTables()
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * Get the exam subjects associated with this subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }
}
