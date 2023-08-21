<?php

namespace Database\Seeders;

use App\Models\Building\FlatTenant;
use Illuminate\Database\Seeder;

class FlatTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FlatTenant::factory()
            ->count(5)
            ->create();
    }
}
