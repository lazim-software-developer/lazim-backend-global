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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
