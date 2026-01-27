<?php

namespace Database\Factories;

use App\Enum\{
    Gender,
    StudentStatus,
};
use App\Helpers\AdmissionNumberHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 2,
            'parent_id' => 1,
            'admission_no' => AdmissionNumberHelper::formatAdmissionNumber(1),
            'class_id' => 1,
            'section_id' => 1,
            'gender' => Gender::MALE->value,
            'dob' => '2010-05-15',
            'address' => fake()->address(),
            'student_status' => StudentStatus::ACTIVE->value,
        ];
    }
}
