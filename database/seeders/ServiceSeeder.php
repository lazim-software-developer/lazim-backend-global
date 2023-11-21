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
            // ['id' => 1, 'name' => 'Cleaning Service', 'custom' => 0, 'active' => 1],
            // ['id' => 2, 'name' => 'MEP Service', 'custom' => 0, 'active' => 1],
            // ['id' => 3, 'name' => 'Security', 'custom' => 0, 'active' => 1],
            // ['id' => 4, 'name' => 'Life Guar', 'custom' => 0, 'active' => 1],
            // ['id' => 5, 'name' => 'Concierge', 'custom' => 0, 'active' => 1],
            // ['id' => 6, 'name' => 'Technical Services', 'custom' => 0, 'active' => 1],
            // ['id' => 7, 'name' => 'Swimming Pool Maintenance', 'custom' => 0, 'active' => 1],
            // ['id' => 8, 'name' => 'Post Control', 'custom' => 0, 'active' => 1],
            // ['id' => 9, 'name' => 'GYM', 'custom' => 0, 'active' => 1],
            // ['id' => 10, 'name' => 'Chiller', 'custom' => 0, 'active' => 1],
            // ['id' => 11, 'name' => 'Water Tank Cleaning', 'custom' => 0, 'active' => 1],
            // ['id' => 12, 'name' => 'Fire System', 'custom' => 0, 'active' => 1],

            ['name' => 'Home Healthcare Service','type' => 'inhouse'],
            ['name' => 'Home Salon','type' => 'inhouse'],
            ['name' => 'Home PCR Test','type' => 'inhouse'],
            ['name' => 'Pet Grooming Service','type' => 'inhouse'],
            ['name' => 'Movers & Packers','type' => 'inhouse'],
            ['name' => 'Ac Maintenance Service','type' => 'inhouse'],
            ['name' => 'Deep Cleaning Services','type' => 'inhouse'],
            ['name' => 'Electrical','type' => 'inhouse'],
            ['name' => 'Plumbing','type' => 'inhouse'],
            ['name' => 'Pest Control','type' => 'inhouse'],
            ['name' => 'Disinfection & Sanitization','type' => 'inhouse'],
        ];

        Service::insert($services);
    }
}
