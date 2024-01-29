<?php

namespace App\Filament\Resources\ComplaintOfficerResource\Pages;

use App\Filament\Resources\ComplaintOfficerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaintOfficer extends EditRecord
{
    protected static string $resource = ComplaintOfficerResource::class;

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
