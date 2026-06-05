<?php

namespace Database\Factories;

use App\Enum\Subjects;
use App\Helpers\TeacherDataFormatHelper;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sequenceNumber = Str::padLeft(1, 5, '0');

        return [
            'user_id' => 3,
            'employee_code' => TeacherDataFormatHelper::employeeCodeFormatter((int) $sequenceNumber),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'specialization' => collect(Subjects::list())->random(),
            'experience_years' => rand(1, 10),
        ];
    }
}
