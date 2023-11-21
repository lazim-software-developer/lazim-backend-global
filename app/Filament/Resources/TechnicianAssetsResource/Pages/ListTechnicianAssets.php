<?php

namespace App\Filament\Resources\TechnicianAssetsResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TechnicianAssetsResource;

class ListTechnicianAssets extends ListRecords
{
    protected static string $resource = TechnicianAssetsResource::class;
    protected function getTableQuery(): Builder
    {
        $buildingsoflogedin = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        return parent::getTableQuery()->whereIn('building_id',$buildingsoflogedin);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
