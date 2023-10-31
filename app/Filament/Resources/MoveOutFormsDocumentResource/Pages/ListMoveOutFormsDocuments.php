<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMoveOutFormsDocuments extends ListRecords
{
    protected static string $resource = MoveOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
           //Actions\CreateAction::make(),
        ];
    }
}
