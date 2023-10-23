<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visitor\FlatDomesticHelp;

class FlatDomesticHelpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FlatDomesticHelp::factory()
            ->count(5)
            ->create();
    }
}
