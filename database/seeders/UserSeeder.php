<?php

namespace Database\Seeders;

use App\Models\Master\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [

            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'role_id' => Role::where('name', 'Admin')->value('id'),
            'phone' => '9234567890',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('test1234'),
            'active' =>1

        ];

        User::create($user);
    }
}
