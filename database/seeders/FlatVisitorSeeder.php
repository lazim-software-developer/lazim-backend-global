<?php

namespace Database\Seeders;

use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Seeder;

class FlatVisitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FlatVisitor::factory()
            ->count(5)
            ->create();
    }
}
