<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\NotificationTypeSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CitySeeder::class,
            FacilitySeeder::class,
            PermissionsTableSeeder::class,
            RoleSeeder::class,
            // ServiceSeeder::class,
            UserSeeder::class,
            ServiceParameterSeeder::class,
            // DocumentLibrarySeeder::class,
            TagsTableSeeder::class,
            NotificationTypeSeeder::class,
        ]);
    }
}
