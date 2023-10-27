<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantDocuments extends ListRecords
{
    protected static string $resource = TenantDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
