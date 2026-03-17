<?php

namespace App\Interfaces;

interface TeacherClassAssignmentInterface extends BaseInterface
{
    /**
     * Gets an active teacher assignment.
     *
     * @param mixed $requiredTuples
     *
     * @return array<mixed, string>
     */
    public function getValidAssignments($requiredTuples): array;
}
