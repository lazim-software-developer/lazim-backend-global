<?php

namespace App\Filament\Resources\Building\BuildingPocResource\Pages;

use App\Filament\Resources\Building\BuildingPocResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuildingPocs extends ListRecords
{
    protected static string $resource = BuildingPocResource::class;
    protected ?string $heading        = 'Security';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
