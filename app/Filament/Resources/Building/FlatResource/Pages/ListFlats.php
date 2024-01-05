<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\FlatResource;

class ListFlats extends ListRecords
{
    protected static string $resource = FlatResource::class;

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
