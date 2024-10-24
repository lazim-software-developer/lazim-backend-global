<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use Filament\Resources\Pages\EditRecord;

class EditTechnicianVendor extends EditRecord
{
    protected static string $resource = TechnicianVendorResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $technicianVendor = $this->record;
        $user             = $technicianVendor->user;
        $vendor           = $technicianVendor->vendor;
        // dd($technicianVendor->vendor->services->pluck('name','id')->toArray());

        return [
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name ?? '',
            'email'             => $user->email,
            'phone'             => $user->phone ?? '',
            'vendor_id'         => $vendor->id ?? null,
            'service_id'        => $technicianVendor->vendor->services->pluck('id')->toArray(),
            'technician_number' => $technicianVendor->technician_number,
            'active'            => $technicianVendor->active,
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        
        return [
            'vendor_id'         => $data['vendor_id'],
            'technician_number' => $data['technician_number'],
            'active'            => $data['active'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
