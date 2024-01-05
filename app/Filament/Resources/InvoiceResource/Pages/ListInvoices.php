<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Vendor\Vendor;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;
    protected static ?string $title = 'Invoice';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereIn('vendor_id',Vendor::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id'));
    }
}
