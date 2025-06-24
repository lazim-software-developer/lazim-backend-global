<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlatTenant extends EditRecord
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title = 'Resident';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
