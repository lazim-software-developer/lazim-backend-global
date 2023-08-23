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
        $roles=[
            ['id'=>1,'name'=>'Owner','building_id'=>'1'],
            ['id'=>2,'name'=>'Vendor','building_id'=>'1'],
            ['id'=>3,'name'=>'Managing Director','building_id'=>'1'],
            ['id'=>4,'name'=>'Financial Manager','building_id'=>'1'],
            ['id'=>5,'name'=>'Building Engineer','building_id'=>'1'],
            ['id'=>6,'name'=>'Operations Engineer','building_id'=>'1'],
            ['id'=>7,'name'=>'Operations Manager','building_id'=>'1'],
            ['id'=>8,'name'=>'Staff','building_id'=>'1']
        ];
            Role::insert($roles);

    }
}
