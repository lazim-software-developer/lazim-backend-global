<?php

namespace Database\Seeders;

use App\Models\Building\Flat;
use Illuminate\Database\Seeder;

class FlatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Flat::factory()
            ->count(5)
            ->create();
    }
}
