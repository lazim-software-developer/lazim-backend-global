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
        $facilities = [
            ['id' => 1, 'name' => 'Swimming Pool', 'icon' => 'dev/images/amenity.jpg',  'active' => 1],
            ['id' => 2, 'name' => 'Party Hall', 'icon' => 'dev/images/amenity.jpg', 'active' => 1],
            ['id' => 3, 'name' => 'Basketball Court', 'icon' => 'dev/images/amenity.jpg', 'active' => 1],
            ['id' => 4, 'name' => 'Turf', 'icon' => 'dev/images/amenity.jpg', 'active' => 1],

        ];

        Facility::insert($facilities);
    }
}
