<?php

namespace Database\Seeders\Local;

use App\Enum\{
    Gender,
    StudentStatus,
};
use App\Helpers\AdmissionNumberHelper;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        Student::truncate();

        $student = [
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

        Student::factory()->create($student);
    }
}
