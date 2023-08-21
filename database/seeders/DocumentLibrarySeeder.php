<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\DocumentLibrary;

class DocumentLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentLibrary::factory()
            ->count(5)
            ->create();
    }
}
