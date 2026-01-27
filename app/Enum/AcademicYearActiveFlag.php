<?php

namespace App\Enum;

/**
 * Enums for academic year active status (academic_years.is_active).
 */
enum AcademicYearActiveFlag: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}
