<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFitOutFormsDocument extends EditRecord
{
    protected static string $resource = FitOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
