<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTechnicianVendor extends CreateRecord
{
    protected static string $resource = TechnicianVendorResource::class;

    protected function afterCreate(): void
    {
        $technician = $this->record;

        $technician->active               = true;
        $technician->owner_association_id = auth()->user()->owner_association_id;
        $technician->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
