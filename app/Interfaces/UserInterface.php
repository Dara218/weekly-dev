<?php

namespace App\Interfaces;

interface UserInterface extends BaseInterface
{
    /**
     * Get the user profile with relationships based on user role.
     *
     * @param int $userId
     *
     * @return \App\Models\User
     */
    public function getRoleBasedProfile(int $userId);
}
