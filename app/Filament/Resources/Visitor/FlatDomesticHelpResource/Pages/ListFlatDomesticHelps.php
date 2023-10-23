<?php

namespace App\Filament\Resources\Visitor\FlatDomesticHelpResource\Pages;

use App\Filament\Resources\Visitor\FlatDomesticHelpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlatDomesticHelps extends ListRecords
{
    protected static string $resource = FlatDomesticHelpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
