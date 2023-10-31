<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoveOutFormsDocument extends EditRecord
{
    protected static string $resource = MoveOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
