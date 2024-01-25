<?php

namespace App\Filament\Resources\LegalOfficerResource\Pages;

use App\Filament\Resources\LegalOfficerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLegalOfficer extends EditRecord
{
    protected static string $resource = LegalOfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
