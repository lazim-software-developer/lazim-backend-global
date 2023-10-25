<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantDocument extends EditRecord
{
    protected static string $resource = TenantDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
