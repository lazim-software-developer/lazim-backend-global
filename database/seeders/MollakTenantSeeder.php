<?php

namespace Database\Seeders;

use App\Models\MollakTenant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MollakTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       MollakTenant::factory()->count(5)->create();
    }
}
