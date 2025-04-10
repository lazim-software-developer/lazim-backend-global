<?php

namespace App\Filament\Resources\Master\CountryResource\Pages;

use App\Filament\Resources\Master\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountry extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
