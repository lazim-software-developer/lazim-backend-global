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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
