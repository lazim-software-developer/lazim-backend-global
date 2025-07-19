<?php

namespace App\Filament\Resources\Master\CityResource\Pages;

use App\Filament\Resources\Master\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
