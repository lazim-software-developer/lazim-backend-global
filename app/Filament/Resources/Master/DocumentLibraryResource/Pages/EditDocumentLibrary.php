<?php

namespace App\Filament\Resources\Master\DocumentLibraryResource\Pages;

use App\Filament\Resources\Master\DocumentLibraryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentLibrary extends EditRecord
{
    protected static string $resource = DocumentLibraryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
