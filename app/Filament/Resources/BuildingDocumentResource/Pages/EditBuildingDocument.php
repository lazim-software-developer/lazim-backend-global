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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
