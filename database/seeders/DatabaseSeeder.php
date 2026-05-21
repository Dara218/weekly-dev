<?php

namespace Database\Seeders;

use Database\Seeders\Local\{
    AcademicYearSeeder,
    ClassesSeeder,
    ParentSeeder,
    SectionSeeder,
    StudentSeeder,
    SubjectSeeder,
    TeacherClassAssignmentSeeder,
    TeacherSeeder,
    UserSeeder,
};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TeacherSeeder::class,
            AcademicYearSeeder::class,
            ClassesSeeder::class,
            SectionSeeder::class,
            TeacherClassAssignmentSeeder::class,
            ParentSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
        ]);
    }
}
