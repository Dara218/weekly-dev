<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
     * Get the issued book records for this book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class);
    }
}
