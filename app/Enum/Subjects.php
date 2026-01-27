<?php

namespace App\Enum;

/**
 * Enum for teacher subject specialization (teachers.specialization).
 */
enum Subjects: string
{
    case MATHEMATICS = 'MATHEMATICS';
    case ENGLISH_LANGUAGE = 'ENGLISH_LANGUAGE';
    case SCIENCE = 'SCIENCE';
    case PHYSICS = 'PHYSICS';
    case CHEMISTRY = 'CHEMISTRY';
    case BIOLOGY = 'BIOLOGY';
    case HISTORY = 'HISTORY';
    case GEOGRAPHY = 'GEOGRAPHY';
    case COMPUTER_SCIENCE = 'COMPUTER_SCIENCE';
    case INFORMATION_TECHNOLOGY = 'INFORMATION_TECHNOLOGY';
    case PHYSICAL_EDUCATION = 'PHYSICAL_EDUCATION';
    case MUSIC = 'MUSIC';
    case ARTS = 'ARTS';
    case FILIPINO = 'FILIPINO';
    case VALUES_EDUCATION = 'VALUES_EDUCATION';
    case ECONOMICS = 'ECONOMICS';
    case ACCOUNTING = 'ACCOUNTING';
    case BUSINESS_STUDIES = 'BUSINESS_STUDIES';
    case SPECIAL_EDUCATION = 'SPECIAL_EDUCATION';
    case GUIDANCE_AND_COUNSELING = 'GUIDANCE_AND_COUNSELING';

    /**
     * Get the list of all enum values.
     *
     * @return array<mixed, string>
     */
    public static function list()
    {
        return array_map(fn(self $enum) => $enum->value, Subjects::cases());
    }
}
