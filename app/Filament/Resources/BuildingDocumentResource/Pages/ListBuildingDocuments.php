<?php

namespace App\Filament\Resources\BuildingDocumentResource\Pages;

use App\Filament\Resources\BuildingDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuildingDocuments extends ListRecords
{
    protected static string $resource = BuildingDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
