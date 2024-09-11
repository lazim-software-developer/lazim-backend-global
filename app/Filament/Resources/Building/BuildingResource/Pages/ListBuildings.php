<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->pluck('building_id');
        if(auth()->user()?->role?->name === 'Property Manager'){
            return parent::getTableQuery()->whereIn('id', $buildingIds);
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        return parent::getTableQuery();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Building')
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    if ($role === 'Admin') {
                        return true;
                    }
                }),
        ];
    }
}
