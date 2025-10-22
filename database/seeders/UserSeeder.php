<?php

namespace Database\Seeders;

use App\Models\Master\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            ['id' => 1,
             'first_name' => 'SuperAdmin',
             'last_name' => '',
             'email' => 'admin@gmail.com',
             'phone' => '9234567890',
             'password' => Hash::make('test1234'),
             'active' => 1,
             'role_id' => Role::where('name', 'Admin')->value('id')
            ],
            ['id' => 1,
             'first_name' => 'System',
             'last_name' => 'internal use',
             'email' => 'system@internal.use',
             'phone' => '011001001010',
             'password' => Hash::make('test1234'),
             'active' => 1,
             'role_id' => Role::where('name', 'Admin')->value('id')
            ],
        ];

        User::insert($user);
    }
}
