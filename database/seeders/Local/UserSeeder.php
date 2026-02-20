<?php

namespace Database\Seeders\Local;

use App\Enum\{
    UserActiveStatus,
    UserRole,
};
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table to prevent duplicate data
        User::truncate();

        $user = [
            [
                // Admin
                'first_name' => 'Admin',
                'middle_name' => 'TestMidA',
                'last_name' => 'User',
                'email' => 'admin_user@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::ADMIN->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Student
                'first_name' => 'John',
                'middle_name' => 'TestMidS',
                'last_name' => 'Student',
                'email' => 'john_student@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::STUDENT->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Teacher
                'first_name' => 'John',
                'middle_name' => 'TestMidT',
                'last_name' => 'Teacher',
                'email' => 'john_teacher@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::TEACHER->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Parent
                'first_name' => 'John',
                'middle_name' => 'TestMidP',
                'last_name' => 'Parent',
                'email' => 'john_parent@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::PARENT->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
        ];

        $count = count($user);

        User::factory($count)
            ->sequence(...$user)
            ->create();
    }
}
