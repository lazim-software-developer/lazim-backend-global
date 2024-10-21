<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTechnicianVendor extends EditRecord
{
    protected static string $resource = TechnicianVendorResource::class;

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
