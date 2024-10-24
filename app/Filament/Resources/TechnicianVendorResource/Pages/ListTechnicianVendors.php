<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTechnicianVendors extends ListRecords
{
    protected static string $resource = TechnicianVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user      = auth()->user();
        $vendorIds = Vendor::where('owner_association_id', $user->owner_association_id)->pluck('id');

        if ($user->role->name == 'Property Manager') {
            // Ensure to use the 'whereIn' method since $vendorIds is now a collection
            return parent::getTableQuery()->whereIn('vendor_id', $vendorIds);
        }
        return parent::getTableQuery();
    }

}
