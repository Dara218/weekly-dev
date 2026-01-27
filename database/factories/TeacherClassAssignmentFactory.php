<?php

namespace Database\Factories;

use App\Enum\TeacherClassAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherClassAssignment>
 */
class TeacherClassAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => 1,
            'class_id' => 1,
            'section_id' => 1,
            'academic_year_id' => 1,
            'status' => TeacherClassAssignmentStatus::ACTIVE->value,
        ];
    }
}
