<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedPmRolePermissions extends Command
{
    protected $signature   = 'permissions:seed-pm';
    protected $description = 'Seed permissions from PM role config file';

    public function handle()
    {

        $pmConfig = config('pm-role-permission.roles.Property Manager.permissions');

        foreach ($pmConfig as $permission) {
            $exists = DB::connection('mysql')
                ->table('permissions')
                ->where('name', $permission)
                ->exists();

            if (! $exists) {
                DB::connection('mysql')->table('permissions')->insert([
                    'name'       => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
