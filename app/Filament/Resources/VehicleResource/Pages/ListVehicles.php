<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin')
        {
            $roles = Role::whereIn('name',['Tenant','Owner'])->pluck('id');
            $users= User::where('owner_association_id',auth()->user()?->owner_association_id)->whereIn('role_id',$roles)->pluck('id');
            return parent::getTableQuery()->whereIn('user_id',$users);
        }
        return parent::getTableQuery();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
