<?php

namespace Database\Seeders\Local;

use App\Enum\{
    ClassesLevel,
    ClassesStatus,
};
use App\Models\Classes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        Classes::truncate();

        $classes = [
            // Grade school
            [
                'name' => ClassesLevel::GRADE_1->value,
                'academic_year_id' => 1, // Current year
                'status' => ClassesStatus::ACTIVE->value,
            ],
            // High school
            [
                'name' => ClassesLevel::GRADE_7->value,
                'academic_year_id' => 2, // Current year
                'status' => ClassesStatus::ACTIVE->value,
            ],
            // College
            [
                'name' => ClassesLevel::COLLEGE_1ST_YEAR->value,
                'academic_year_id' => 1, // Current year
                'status' => ClassesStatus::ACTIVE->value,
            ],
        ];

        $count = count($classes);

        Classes::factory($count)
            ->sequence(...$classes)
            ->create();
    }
}
