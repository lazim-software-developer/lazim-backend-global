<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use App\Models\Master\Role;
use DB;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAssetMaintenances extends ListRecords
{
    protected static string $resource = AssetMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id')
            ->toArray();

        $userRole = Role::find(auth()->user()->role_id)?->name;

        if ($userRole === 'Admin') {
            return parent::getTableQuery();
        }

        if ($userRole === 'Property Manager') {
            return parent::getTableQuery()->whereIn('building_id', $buildingIds);
        }

        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()?->id);
    }
}
