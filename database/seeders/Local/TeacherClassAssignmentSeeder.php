<?php

namespace Database\Seeders\Local;

use App\Enum\TeacherClassAssignmentStatus;
use App\Models\TeacherClassAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherClassAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        TeacherClassAssignment::truncate();

        $data = [
            [
                'teacher_id' => 1,
                'class_id' => 1,
                'section_id' => 1,
                'academic_year_id' => 1,
                'status' => TeacherClassAssignmentStatus::ACTIVE->value,
            ],
            [
                'teacher_id' => 1,
                'class_id' => 2,
                'section_id' => 2,
                'academic_year_id' => 2,
                'status' => TeacherClassAssignmentStatus::ACTIVE->value,
            ],
        ];

        $count = count($data);

        TeacherClassAssignment::factory($count)
            ->sequence(...$data)
            ->create();
    }
}
