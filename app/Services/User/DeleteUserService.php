<?php

namespace App\Services\User;

use App\Interfaces\UserInterface;

class DeleteUserService
{
    /**
     * Repository interface for user data operations.
     *
     * @var \App\Interfaces\UserInterface
     */
    protected UserInterface $userInterface;

    /**
     * Initialize the service.
     *
     * @param \App\Interfaces\UserInterface $userInterface
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Load the authenticated user's profile with role-based relationships.
     *
     * @param int $userId
     *
     * @return \App\Models\User
     */
    public function loadUserProfile(int $userId)
    {
        return $this->userInterface->getRoleBasedProfile($userId);
    }
}
