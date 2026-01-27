<?php

namespace Database\Seeders\Local;

use App\Enum\AcademicYearActiveFlag;
use App\Helpers\AcademicYearFormatHelper;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        AcademicYear::truncate();

        $academicYears = [
            // Current year
            [
                'name' => AcademicYearFormatHelper::academicYearFormatter(),
                'is_active' => AcademicYearActiveFlag::ACTIVE->value,
            ],
            // Last year
            [
                'name' => AcademicYearFormatHelper::previousAcademicYearFormatter(),
                'is_active' => AcademicYearActiveFlag::INACTIVE->value,
            ],
        ];

        $count = count($academicYears);

        AcademicYear::factory($count)
            ->sequence(...$academicYears)
            ->create();
    }
}
