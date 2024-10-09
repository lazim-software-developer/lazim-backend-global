<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOwnerAssociationInvoices extends ListRecords
{
    protected static string $resource = OwnerAssociationInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
           Action::make('Generate Invoice')->url(function() {
                if (in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])) {
                    return '/app/generate-invoice';
                } else {
                    return '/admin/generate-invoice';
                }
            })

        ];
    }
    protected function getTableQuery(): Builder
    {
        if(in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id',Filament::getTenant()->id);
    }
}
