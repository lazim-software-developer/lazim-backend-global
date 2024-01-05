<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\Building\Building;
use Illuminate\Contracts\View\View;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\FlatTenantResource;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title = 'Residents';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {
        $building = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
        {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('building_id',$building);
    }
}
