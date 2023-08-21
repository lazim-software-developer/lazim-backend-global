<?php

namespace App\Filament\Resources\TenantComplaints\TenantcomplaintResource\Pages;

use App\Filament\Resources\TenantComplaints\TenantcomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantcomplaint extends EditRecord
{
    protected static string $resource = TenantcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
