<?php

namespace Database\Factories;

use App\Enum\ClassesLevel;
use App\Enum\ClassesStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classes>
 */
class ClassesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => collect(ClassesLevel::list())->random(),
            'academic_year_id' => 1, // Current year
            'status' => collect(ClassesStatus::list())->random(),
        ];
    }
}
