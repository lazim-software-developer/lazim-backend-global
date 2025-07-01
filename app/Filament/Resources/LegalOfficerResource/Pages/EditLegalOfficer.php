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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
