<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoveInFormsDocument extends EditRecord
{
    protected static string $resource = MoveInFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
