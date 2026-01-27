<?php

namespace Database\Seeders\Local;

use App\Enum\Subjects;
use App\Helpers\TeacherDataFormatHelper;
use App\Interfaces\UserInterface;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table to prevent duplicate data
        Teacher::truncate();

        $userId = app(UserInterface::class)->find(3)->id; // ID:3 from UserSeeder
        $sequenceNumber = Str::padLeft(1, 5, '0');

        $teacher = [
            'user_id' => $userId,
            'employee_code' => TeacherDataFormatHelper::employeeCodeFormatter((int) $sequenceNumber),
            'phone' => '09123456789',
            'specialization' => Subjects::HISTORY->value,
            'experience_years' => 4,
        ];

        Teacher::factory()->create($teacher);
    }
}
