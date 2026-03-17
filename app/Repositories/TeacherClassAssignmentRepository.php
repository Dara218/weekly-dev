<?php

namespace App\Repositories;

use App\Enum\TeacherClassAssignmentStatus;
use App\Interfaces\TeacherClassAssignmentInterface;
use App\Models\TeacherClassAssignment;

class TeacherClassAssignmentRepository extends BaseRepository implements TeacherClassAssignmentInterface
{
    /**
     * Setup the repository.
     *
     * @param \App\Models\TeacherClassAssignment $model
     */
    public function __construct(TeacherClassAssignment $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getValidAssignments($requiredTuples): array
    {
        /** @var \Illuminate\Support\Collection<int, \App\Models\TeacherClassAssignment> $assignments */
        $assignments = $this->model
            ->where('status', TeacherClassAssignmentStatus::ACTIVE->value)
            ->whereIn('teacher_id', $requiredTuples->pluck('teacher_id')->unique())
            ->get(['teacher_id', 'class_id', 'section_id']);

        return $assignments
            ->map(fn (TeacherClassAssignment $row) => "{$row->teacher_id}-{$row->class_id}-{$row->section_id}")
            ->toArray();
    }
}
