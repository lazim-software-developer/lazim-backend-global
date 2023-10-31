<?php

namespace App\Filament\Resources\FlatDocumentResource\Pages;

use App\Filament\Resources\FlatDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlatDocument extends EditRecord
{
    protected static string $resource = FlatDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
