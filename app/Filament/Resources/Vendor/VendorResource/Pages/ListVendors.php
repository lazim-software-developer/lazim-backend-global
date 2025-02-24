<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use App\Filament\Resources\Vendor\VendorResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;
    protected function getTableQuery(): Builder
    {
        $vendors = DB::table('owner_association_vendor')
            ->where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()->owner_association_id)
            ->pluck('vendor_id');
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('id', $vendors);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
