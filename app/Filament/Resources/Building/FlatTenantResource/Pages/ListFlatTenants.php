<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
