<?php

namespace Database\Seeders;

use App\Models\Master\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $services = [
            ['name' => 'Home Healthcare Service','type' => 'inhouse', 'code' => 'in_1'],
            ['name' => 'Home Salon','type' => 'inhouse', 'code' => 'in_2'],
            ['name' => 'Home PCR Test','type' => 'inhouse', 'code' => 'in_3'],
            ['name' => 'Pet Grooming Service','type' => 'inhouse', 'code' => 'in_4'],
            ['name' => 'Movers & Packers','type' => 'inhouse', 'code' => 'in_5'],
            ['name' => 'Ac Maintenance Service','type' => 'inhouse', 'in_6'],
            ['name' => 'Deep Cleaning Services','type' => 'inhouse', 'in_7'],
            ['name' => 'Electrical','type' => 'inhouse', 'in_8'],
            ['name' => 'Plumbing','type' => 'inhouse', 'in_9'],
            ['name' => 'Pest Control','type' => 'inhouse', 'in_10'],
            ['name' => 'Disinfection & Sanitization','type' => 'inhouse', 'in_11'],
        ];

        Service::insert($services);
    }
}
