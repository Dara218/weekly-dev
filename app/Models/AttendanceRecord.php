<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    /**
     * Get the class associated with this attendance record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    /**
     * Get the section associated with this attendance record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the teacher who marked this attendance record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'marked_by');
    }

    /**
     * Get the attendance details for this attendance record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceDetails()
    {
        return $this->hasMany(AttendanceDetail::class);
    }
}
