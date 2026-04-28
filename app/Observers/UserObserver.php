<?php

namespace App\Observers;

use App\Models\{
    Student,
    User,
};

class UserObserver
{
    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        $user->student()->delete();
    }

    /**
     * Handle the User "restoring" event.
     */
    public function restoring(User $user): void
    {
        Student::withTrashed()
            ->where('user_id', $user->id)
            ->restore();
    }
}
