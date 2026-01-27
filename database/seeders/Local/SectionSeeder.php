<?php

namespace Database\Seeders\Local;

use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        Section::truncate();

        $section = [
            [
                'section_name' => 'Section Test',
                'room_number' => 14,
                'class_id' => 1,
                'teacher_id' => 1,
                'capacity' => 30,
            ],
            [
                'section_name' => 'Section Test 2',
                'room_number' => 21,
                'class_id' => 2,
                'teacher_id' => 1,
                'capacity' => 30,
            ],
        ];

        $count = count($section);

        Section::factory($count)
            ->sequence(...$section)
            ->create();
    }
}
