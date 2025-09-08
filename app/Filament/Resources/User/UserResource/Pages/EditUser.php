<?php

namespace App\Filament\Resources\User\UserResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\User\UserResource;
use BezhanSalleh\FilamentShield\Support\Utils;


class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public Collection $permissions;
    public Collection $accountPermissions;
    public Collection $nonAccountPermissions;


    public function getTitle(): string
    {
        return 'Edit User';
    }
    public function mount($record): void
    {
        parent::mount($record);

        try {
            $user = $this->record;

            $conn = DB::connection(env('SECOND_DB_CONNECTION', 'lazim_accounts'));

            $remoteUser = $conn->table('users')
                ->where('oa_user_id', $user->id)
                ->first();

            if ($remoteUser) {
                $permissions = $conn->table('model_has_permissions')
                    ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
                    ->where('model_has_permissions.model_id', $remoteUser->id)
                    ->pluck('permissions.name')
                    ->toArray();

                // ✅ Merge safely with fallback
                $this->form->fill(array_merge(
                    $this->data ?? [],
                    ['accounts_permission' => $permissions]
                ));
            }
        } catch (\Throwable $e) {
            \Log::error('EditUser mount() failed: ' . $e->getMessage());
        }
    }


    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     $this->permissions = collect([
    //         ...($data['pages_tab'] ?? []),
    //         ...($data['widgets_tab'] ?? []),
    //         ...array_merge(...array_values($data['resource'] ?? [])),
    //     ]);
    //     dd($data, $this);
    //     return $data; // Let rest of data be saved normally

    // }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $excludeKeys = ['first_name', 'last_name', 'email', 'phone', 'roles', 'active', 'guard_name', 'accounts_permission'];
        $this->accountPermissions = collect($data['accounts_permission'] ?? [])->flatten();
        $this->nonAccountPermissions = collect($data)
            ->reject(fn($value, $key) => in_array($key, $excludeKeys)) // sirf permissions ka data chhodo
            ->flatMap(fn($permissions) => is_array($permissions) ? $permissions : []) // flatten to one-level array
            ->filter();

        return Arr::only($data, ['first_name', 'last_name', 'email', 'phone', 'roles', 'active', 'guard_name']);
    }


    protected function afterSave()
    {

        if ($this->data['roles']) {
            $user = User::find($this->record->id);
            $user->update([
                'role_id' => is_string($this->data['roles']) ? $this->data['roles'] : $this->data['roles'][0]
            ]);
        }

        $nonAccountPermissionModels = collect();
        $this->nonAccountPermissions->each(function ($permission) use ($nonAccountPermissionModels) {
            $nonAccountPermissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => !is_null($this->data['guard_name']) ?  $this->data['guard_name'] : 'web',
            ]));
        });

        // dd($nonAccountPermissionModels);
        $this->record->syncPermissions($nonAccountPermissionModels);
        // dd($this->record, $this->accountPermissions);
        $this->updateUserInAccounting($this->record, $this->accountPermissions);
    }


    protected function updateUserInAccounting($user, $permissions): void
    {
        // dd($user, $permissions);
        try {
            $conn = DB::connection(env('SECOND_DB_CONNECTION', 'lazim_accounts'));
            \Log::info('lazim_accounts Connection: ' . ($conn->getPdo() ? 'Success' : 'Failed'));

            // Remote user record dhundo
            $existingUser = $conn->table('users')
                ->where('oa_user_id', $user->id)
                ->first();

            if ($existingUser) {
                // Optional: user ke basic info ko update karo (agar zarurat ho)
                $conn->table('users')
                    ->where('id', $existingUser->id)
                    ->update([
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                    ]);

                $accountingUserId = $existingUser->id;
                \Log::info('Updated user in lazim_accounts with ID: ' . $accountingUserId);

                // Handle permissions (string or object dono)
                foreach ($permissions as $permission) {
                    $permissionName = is_object($permission) ? $permission->name : $permission;

                    $permissionRecord = $conn->table('permissions')->where([
                        'name' => $permissionName,
                        'guard_name' => $user->guard_name ?? 'web',
                    ])->first();

                    if (!$permissionRecord) {
                        $permissionId = $conn->table('permissions')->insertGetId([
                            'name' => $permissionName,
                            'guard_name' => $user->guard_name ?? 'web',
                        ]);
                    } else {
                        $permissionId = $permissionRecord->id;
                    }

                    $alreadyExists = $conn->table('model_has_permissions')->where([
                        'model_id' => $accountingUserId,
                        'permission_id' => $permissionId,
                        'model_type' => 'App\\Models\\User',
                    ])->exists();

                    if (!$alreadyExists) {
                        $conn->table('model_has_permissions')->insert([
                            'model_id' => $accountingUserId,
                            'permission_id' => $permissionId,
                            'model_type' => 'App\\Models\\User',
                        ]);

                        \Log::info("Assigned permission '{$permissionName}' to user ID {$accountingUserId}");
                    }
                }
            } else {
                \Log::warning('Accounting user not found, cannot update. Consider calling syncUserToAccounting.');
                // Optional: call $this->syncUserToAccounting($user, $permissions);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update user in accounting DB: ' . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
