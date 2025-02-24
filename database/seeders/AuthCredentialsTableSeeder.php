<?php

namespace Database\Seeders;

use App\Models\AuthCredential;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthCredentialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AuthCredential::create([
            'client_id' => 'CLNT-1234-5678-9101',
            'api_key' => 'AK-1a2b3c4d-5e6f7g8h-9i0j1k2l',
            'module' => AuthCredential::TALLY_MODULE,
            'owner_association_id' => 1,
        ]);
    }
}
