<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Building\FacilityBooking;

class FacilityBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacilityBooking::factory()
            ->count(5)
            ->create();
    }
}
