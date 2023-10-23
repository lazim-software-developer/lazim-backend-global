<?php

namespace Database\Seeders;

use App\Models\Master\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::create(['name'=>'Dubai']);
    }
}
