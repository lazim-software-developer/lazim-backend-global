<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\CreateRecord;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;
    public Collection $nonAccountPermissions;
    public Collection $accountPermissions;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $this->accountPermissions = collect($data['accounts_permission'] ?? [])->flatten();

        $this->nonAccountPermissions = collect($data)
            ->filter(function ($permission, $key) {
                return !in_array($key, ['name', 'guard_name', 'select_all', 'accounts_permission']);
            })
            ->values()
            ->flatten();

        $data['owner_association_id'] = auth()->user()?->owner_association_id;

        return Arr::only($data, ['name', 'guard_name', 'owner_association_id']);
    }
    protected function afterCreate(): void
    {
        $nonAccountPermissionModels = collect();
        $this->nonAccountPermissions->each(function ($permission) use ($nonAccountPermissionModels) {
            $nonAccountPermissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        // Role ke sath sirf non-accounts permissions sync karo
        $this->record->syncPermissions($nonAccountPermissionModels);

        $this->syncRoleToAccounting($this->record, $this->accountPermissions);
    }

    protected function syncRoleToAccounting($role, $permissions): void
    {
        try {
            $conn = DB::connection(env('SECOND_DB_CONNECTION', 'lazim_accounts'));

            Log::info('Connecting to lazim_accounts DB: ' . ($conn->getPdo() ? 'Success' : 'Failed'));

            $roleData = [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'oa_role_id' => $role->id,
            ];

            $conn->table('roles')->updateOrInsert(
                ['oa_role_id' => $role->id, 'guard_name' => $role->guard_name],
                $roleData
            );
            $existingRole = $conn->table('roles')
                ->where('oa_role_id', $role->id)
                ->where('guard_name', $role->guard_name)
                ->first();

            if (!$existingRole) {
                throw new \Exception('Failed to create or update role in accounting DB');
            }
            $accountingRoleId = $existingRole->id;
            Log::info('Role synced to accounting DB with ID: ' . $accountingRoleId);

            $existingPermissionIds = [];
            foreach ($permissions as $permissionName) {
                // Upsert permission
                $conn->table('permissions')->updateOrInsert(
                    ['name' => $permissionName, 'guard_name' => $role->guard_name],
                    ['name' => $permissionName, 'guard_name' => $role->guard_name]
                );

                $permissionRecord = $conn->table('permissions')
                    ->where('name', $permissionName)
                    ->where('guard_name', $role->guard_name)
                    ->first();

                if ($permissionRecord) {
                    $existingPermissionIds[] = $permissionRecord->id;
                    Log::info('Permission synced: ' . $permissionName . ' with ID: ' . $permissionRecord->id);
                } else {
                    Log::error('Failed to fetch permission ID for: ' . $permissionName);
                }
            }

            $conn->table('role_has_permissions')
                ->where('role_id', $accountingRoleId)
                ->whereNotIn('permission_id', $existingPermissionIds)
                ->delete();
            Log::info('Deleted old role_has_permissions for role ID: ' . $accountingRoleId);

            foreach ($existingPermissionIds as $permissionId) {
                $conn->table('role_has_permissions')->updateOrInsert(
                    ['role_id' => $accountingRoleId, 'permission_id' => $permissionId]
                );
                Log::info('Role_has_permissions synced for role ID: ' . $accountingRoleId . ', permission ID: ' . $permissionId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync role to accounting DB: ' . $e->getMessage());
        }
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
