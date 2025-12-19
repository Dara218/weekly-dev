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
                'name' => 'Admin User',
                'email' => 'admin_user@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::ADMIN->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Student
                'name' => 'John Student',
                'email' => 'john_student@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::STUDENT->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Teacher
                'name' => 'John Teacher',
                'email' => 'john_teacher@example.com',
                'password' => bcrypt('Qwerty123@'),
                'role' => UserRole::TEACHER->value,
                'profile_image' => null,
                'is_active' => UserActiveStatus::ACTIVE->value,
                'remember_token' => Str::random(10),
            ],
            [
                // Parent
                'name' => 'John Parent',
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
