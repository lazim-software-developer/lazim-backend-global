<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tags = [
            ['name' => 'Civil', 'active' => true],
            ['name' => 'MIP', 'active' => true],
            ['name' => 'Security', 'active' => true],
            ['name' => 'Cleaning', 'active' => true],
            ['name' => 'Others', 'active' => true],
        ];

        DB::table('tags')->insert($tags);
    }
}
