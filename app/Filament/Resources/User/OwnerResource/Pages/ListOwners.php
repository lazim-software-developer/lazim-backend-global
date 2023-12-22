<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use Filament\Actions;
use App\Models\Building\Flat;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\User\OwnerResource;
use App\Models\FlatOwners;

class ListOwners extends ListRecords
{
    protected static string $resource = OwnerResource::class;
    protected function getTableQuery(): Builder
    {
        $BuildingId = Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id');
        $flatsId = Flat::whereIn('building_id',$BuildingId)->pluck('id');
        $flatowners = FlatOwners::whereIn('flat_id',$flatsId)->pluck('owner_id');
        return parent::getTableQuery()->whereIn('id',$flatowners);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
