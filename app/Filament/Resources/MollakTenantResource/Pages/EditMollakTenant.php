<?php

namespace App\Filament\Resources\MollakTenantResource\Pages;

use App\Filament\Resources\MollakTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMollakTenant extends EditRecord
{
    protected static string $resource = MollakTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\DeleteAction::make(),
        ];
    }
}
