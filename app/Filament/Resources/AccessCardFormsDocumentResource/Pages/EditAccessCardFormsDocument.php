<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccessCardFormsDocument extends EditRecord
{
    protected static string $resource = AccessCardFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
