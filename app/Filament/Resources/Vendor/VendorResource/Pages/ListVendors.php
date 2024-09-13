<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use App\Filament\Resources\Vendor\VendorResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;
    protected function getTableQuery(): Builder
    {
        // if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        // }
        // return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()?->id);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
