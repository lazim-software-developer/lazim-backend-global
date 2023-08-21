<?php

namespace Database\Seeders;

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
            'role_id' => 1,
            'phone' => '9234567890',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),

        ];

        User::create($user);
    }
}
