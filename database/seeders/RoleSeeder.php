<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Create admin role
            $adminRole = Role::create([
                'name' => 'Admin',
                'guard_name' => 'web'
            ]);

            // Get all permissions and attach to admin
            $permissions = Permission::all();

            if ($permissions->count() > 0) {
                $adminRole->syncPermissions($permissions);
                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\Models\User\User',
                    'model_id' => 1
                ]);
                Log::info('Admin role created with ' . $permissions->count() . ' permissions');

            } else {
                Log::warning('No permissions found to attach to admin role');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin role or attach permissions: ' . $e->getMessage());
            throw $e;
        }
    }
}
