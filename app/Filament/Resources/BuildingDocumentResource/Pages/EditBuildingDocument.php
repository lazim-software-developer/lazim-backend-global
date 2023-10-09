<?php

namespace App\Filament\Resources\BuildingDocumentResource\Pages;

use App\Filament\Resources\BuildingDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuildingDocument extends EditRecord
{
    protected static string $resource = BuildingDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
