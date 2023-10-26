<?php

namespace Database\Seeders;

use App\Models\Master\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            ['id'   => 1, 'first_name' => 'Shilpa', 'last_name'=> 'Gowda', 'email' => 'admin@gmail.com','phone' => '9234567890', 'password' => Hash::make('test1234'), 'active' => 1, 'role_id'=> Role::where('name', 'Admin')->value('id'),],
        ];

        User::insert($user);
    }
}
