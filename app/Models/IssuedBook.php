<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedBook extends Model
{
    /**
     * Get the book that was issued.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the student to whom the book was issued.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'issued_to_student_id');
    }
}
