<?php

namespace Database\Seeders;

use App\Models\Master\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'Owner'],
            ['id' => 2, 'name' => 'Vendor'],
            ['id' => 3, 'name' => 'Managing Director'],
            ['id' => 4, 'name' => 'Financial Manager'],
            ['id' => 5, 'name' => 'Building Engineer'],
            ['id' => 6, 'name' => 'Operations Engineer'],
            ['id' => 7, 'name' => 'Operations Manager'],
            ['id' => 8, 'name' => 'Staff'],
            ['id' => 9, 'name' => 'Admin'],
            ['id' => 10, 'name' => 'OA'],
            ['id' => 11, 'name' => 'Tenant'],
        ];
        Role::insert($roles);

    }
}
