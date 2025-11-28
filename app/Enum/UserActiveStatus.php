<?php

namespace App\Enum;

/**
 * Enums for user status (users.is_active).
 */
enum UserActiveStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}
