<?php

namespace App\Filament\Resources\AssetResource\Pages;

use Filament\Actions;
use App\Filament\Resources\AssetResource;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;
    protected static ?string $title = 'Assets';
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
