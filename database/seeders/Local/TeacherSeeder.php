<?php

namespace Database\Seeders\Local;

use App\Enum\Subjects;
use App\Enum\UserRole;
use App\Helpers\TeacherDataFormatHelper;
use App\Interfaces\UserInterface;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        for ($index = 1; $index <= 5; $index++) {
            $user = app(UserInterface::class)->create([
                'email' => "john_teacher{$index}@example.com",
                'first_name' => "Teacher{$index}",
                'password' => bcrypt('Qwerty123@'),
                'last_name' => "Test",
                'role' => UserRole::TEACHER,
                'is_active' => $index % 2,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'employee_code' => TeacherDataFormatHelper::employeeCodeFormatter($index),
                'phone' => '0912345678' . $index,
                'specialization' => Subjects::cases()[array_rand(Subjects::cases())]->value,
                'experience_years' => rand(1, 10),
            ]);
        }
    }
}
