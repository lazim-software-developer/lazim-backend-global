<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Adding an admin user
        $user = \App\Models\User\User::factory()
            ->count(1)
            ->create([
                'email' => 'admin@admin.com',
                'password' => \Hash::make('admin'),
            ]);

        $this->call(AttendanceSeeder::class);
        $this->call(BuildingSeeder::class);
        $this->call(BuildingPocSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(ComplaintSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(DocumentSeeder::class);
        $this->call(DocumentLibrarySeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(FacilityBookingSeeder::class);
        $this->call(FlatSeeder::class);
        $this->call(FlatDomesticHelpSeeder::class);
        $this->call(FlatTenantSeeder::class);
        $this->call(FlatVisitorSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(VendorSeeder::class);
    }
}
