<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianVendors extends ListRecords
{
    protected static string $resource = TechnicianVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
