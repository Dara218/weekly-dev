<?php

namespace Database\Seeders\Local;

use App\Enum\Subjects as SubjectEnum;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        Subject::truncate();

        $subjects = [];

        // Your teacher IDs
        $teacherIds = [5, 6, 7, 8, 9];

        foreach (SubjectEnum::cases() as $index => $subject) {
            // Distribute subjects across teachers
            $teacherId = $teacherIds[$index % count($teacherIds)];

            $subjects[] = [
                'subject_name' => Str::title(str_replace('_', ' ', $subject->value)),
                'subject_code' => 'SUBJ' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'class_id' => 1,
                'teacher_id' => $teacherId,
                'max_marks' => 100,
            ];
        }

        Subject::insert($subjects);
    }
}