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
            //Actions\DeleteAction::make(),
        ];
    }
}
