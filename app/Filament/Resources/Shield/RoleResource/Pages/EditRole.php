<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use Filament\Actions;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\EditRecord;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;
    public Collection $nonAccountPermissions;
    public Collection $accountPermissions;

    public function getTitle(): string
    {
        return 'Edit Role';
    }
    public function mount($record): void
    {
        parent::mount($record);

        try {
            $role = $this->record;

            $conn = DB::connection(env('SECOND_DB_CONNECTION', 'lazim_accounts'));

            $remoteRole = $conn->table('roles')
                ->where('oa_role_id', $role->id)
                ->first();

            if ($remoteRole) {
                $permissions = $conn->table('role_has_permissions')
                    ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->where('role_has_permissions.role_id', $remoteRole->id)
                    ->pluck('permissions.name')
                    ->toArray();

                // ✅ Merge safely using $this->data (fallback)
                $this->form->fill(array_merge(
                    $this->data ?? [],
                    ['accounts_permission' => $permissions]
                ));
            }
        } catch (\Throwable $e) {
            Log::error('EditRole mount() failed: ' . $e->getMessage());
        }
    }


    protected function getActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->accountPermissions = collect($data['accounts_permission'] ?? [])->flatten();

        // Non-accounts permissions
        $this->nonAccountPermissions = collect($data)
            ->filter(function ($permission, $key) {
                return !in_array($key, ['name', 'guard_name', 'select_all', 'accounts_permission']);
            })
            ->values()
            ->flatten();

        return Arr::only($data, ['name', 'guard_name']);
    }

    protected function afterSave(): void
    {
        // Sync non-accounts permissions (local)
        $nonAccountPermissionModels = collect();
        $this->nonAccountPermissions->each(function ($permission) use ($nonAccountPermissionModels) {
            $nonAccountPermissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        $this->record->syncPermissions($nonAccountPermissionModels);
        // dd($this->updateRoleInAccounting);
        // Sync accounts permissions (remote)
        // dd()
        $this->updateRoleInAccounting($this->record, $this->accountPermissions);
    }

    protected function updateRoleInAccounting($role, $permissions): void
    {
        try {
            $conn = DB::connection(env('SECOND_DB_CONNECTION', 'lazim_accounts'));
            Log::info('lazim_accounts Connection: ' . ($conn->getPdo() ? 'Success' : 'Failed'));

            $existingRole = $conn->table('roles')
                ->where('oa_role_id', $role->id)
                ->where('guard_name', $role->guard_name)
                ->first();

            if ($existingRole) {
                $conn->table('roles')
                    ->where('id', $existingRole->id)
                    ->update([
                        'name' => $role->name,
                    ]); // Removed guard_name update to avoid mismatch

                $accountingRoleId = $existingRole->id;
                Log::info('Updated role in lazim_accounts with ID: ' . $accountingRoleId);

                // Handle permissions (support both objects and strings)
                foreach ($permissions as $permission) {
                    $permissionName = is_object($permission) ? $permission->name : $permission;
                    $permissionRecord = $conn->table('permissions')->where([
                        'name' => $permissionName,
                        'guard_name' => $role->guard_name,
                    ])->first();

                    if (!$permissionRecord) {
                        $permissionId = $conn->table('permissions')->insertGetId([
                            'name' => $permissionName,
                            'guard_name' => $role->guard_name,
                        ]);
                    } else {
                        $permissionId = $permissionRecord->id;
                    }

                    $alreadyExists = $conn->table('role_has_permissions')->where([
                        'role_id' => $accountingRoleId,
                        'permission_id' => $permissionId,
                    ])->exists();

                    if (!$alreadyExists) {
                        $conn->table('role_has_permissions')->insert([
                            'role_id' => $accountingRoleId,
                            'permission_id' => $permissionId,
                        ]);
                        Log::info('Added permission: ' . $permissionName . ' for role ID ' . $accountingRoleId);
                    }
                }
            } else {
                Log::warning('Role not found, calling syncRoleToAccounting');
                $this->updateRoleInAccounting($role, $permissions);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update role in accounting DB: ' . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
