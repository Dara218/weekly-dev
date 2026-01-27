<?php

namespace Database\Seeders\Local;

use App\Models\Parents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate the table to prevent duplicate data
        Parents::truncate();

        $parent = [
            'user_id' => 4,
            'phone' => '09123456789',
            'occupation' => 'Developer',
            'address'=> 'Manila City',
        ];

        Parents::factory()->create($parent);
    }
}
