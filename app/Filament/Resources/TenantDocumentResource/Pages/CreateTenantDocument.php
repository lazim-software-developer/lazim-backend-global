<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantDocument extends CreateRecord
{
    protected static string $resource = TenantDocumentResource::class;
}
