<?php

namespace Database\Seeders;

use App\Models\Master\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facility::factory()
            ->count(5)
            ->create();
    }
}
