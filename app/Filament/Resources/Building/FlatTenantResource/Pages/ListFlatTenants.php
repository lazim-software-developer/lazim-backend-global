<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Illuminate\Contracts\View\View;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\FlatTenantResource;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {   
        $building = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        if(auth()->user()->id == 1)
        {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('building_id',$building);
    }
}
