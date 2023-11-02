<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
