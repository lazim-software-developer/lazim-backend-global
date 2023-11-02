<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use App\Filament\Resources\Vendor\VendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
