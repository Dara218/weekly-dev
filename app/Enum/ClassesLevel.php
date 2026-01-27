<?php

namespace App\Enum;

/**
 * Enums for classes (classes.name).
 */
enum ClassesLevel: string
{
    case KINDERGARTEN = 'KINDERGARTEN';

    case GRADE_1  = 'GRADE_1';
    case GRADE_2  = 'GRADE_2';
    case GRADE_3  = 'GRADE_3';
    case GRADE_4  = 'GRADE_4';
    case GRADE_5  = 'GRADE_5';
    case GRADE_6  = 'GRADE_6';

    case GRADE_7  = 'GRADE_7';
    case GRADE_8  = 'GRADE_8';
    case GRADE_9  = 'GRADE_9';
    case GRADE_10 = 'GRADE_10';

    case GRADE_11 = 'GRADE_11';
    case GRADE_12 = 'GRADE_12';

    case COLLEGE_1ST_YEAR = 'COLLEGE_1ST_YEAR';
    case COLLEGE_2ND_YEAR = 'COLLEGE_2ND_YEAR';
    case COLLEGE_3RD_YEAR = 'COLLEGE_3RD_YEAR';
    case COLLEGE_4TH_YEAR = 'COLLEGE_4TH_YEAR';
    case COLLEGE_5TH_YEAR = 'COLLEGE_5TH_YEAR';

    case POSTGRADUATE = 'POSTGRADUATE';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, ClassesLevel::cases());
    }
}
