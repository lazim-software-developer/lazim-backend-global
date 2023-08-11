<?php

namespace Database\Seeders;

use App\Models\Building\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Building::factory()
            ->count(5)
            ->create();
    }
}
