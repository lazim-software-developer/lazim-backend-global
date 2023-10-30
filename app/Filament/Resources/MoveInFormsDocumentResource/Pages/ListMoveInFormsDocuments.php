<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMoveInFormsDocuments extends ListRecords
{
    protected static string $resource = MoveInFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
