<?php

namespace App\Filament\Resources\User\UserResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\User\UserResource;
use BezhanSalleh\FilamentShield\Support\Utils;


class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public Collection $permissions;

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
        $this->permissions = collect($data)
            ->filter(function ($permission, $key) {
                return ! in_array($key, ['name', 'guard_name', 'select_all']);
            })
            ->values()
            ->flatten();

        return Arr::only($data, ['name', 'guard_name']);
    }


    protected function afterSave()
    {

        if ($this->data['roles']) {
            $user = User::find($this->record->id);
            $user->update([
                'role_id' => is_string($this->data['roles']) ? $this->data['roles'] : $this->data['roles'][0]
            ]);
        }


        $permissionModels = collect();
        $this->permissions->each(function ($permission) use ($permissionModels) {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => !is_null($this->data['guard_name']) ?  $this->data['guard_name'] : 'web', //TODO check why we are not able to get guard name from the auth
            ]));
        });

        $this->record->syncPermissions($permissionModels);
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
