<?php

namespace Database\Seeders;

use App\Models\Building\BuildingPoc;
use Illuminate\Database\Seeder;

class BuildingPocSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BuildingPoc::factory()
            ->count(5)
            ->create();
    }
}
