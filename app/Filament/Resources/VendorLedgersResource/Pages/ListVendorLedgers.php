<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VendorLedgersResource;
use App\Models\Building\Building;
use App\Models\Vendor\Vendor;

class ListVendorLedgers extends ListRecords
{
    protected static string $resource = VendorLedgersResource::class;
    protected static ?string $title = 'Service provider ledgers';
    protected function getTableQuery(): Builder
    {
        // $vendor_id = Vendor::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        // return parent::getTableQuery()->where('status','approved')->whereIn('vendor_id', $vendor_id);
        $buildings = Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id');
        return parent::getTableQuery()->where('status','approved')->whereIn('building_id', $buildings);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
