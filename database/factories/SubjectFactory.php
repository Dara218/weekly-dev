<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_name' => 'Test Subject',
            'subject_code' => 'TS1',
            'class_id' => 1,
            'teacher_id' => 1,
            'max_marks' => 100,
        ];
    }
}
