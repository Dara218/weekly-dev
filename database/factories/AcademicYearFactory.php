<?php

namespace Database\Factories;

use App\Enum\AcademicYearActiveFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => '2024–2025',
            'is_active' => AcademicYearActiveFlag::ACTIVE->value,
        ];
    }
}
