<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOwnerAssociationInvoices extends ListRecords
{
    protected static string $resource = OwnerAssociationInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('Generate Invoice')->url('/admin/generate-invoice')
        ];
    }
}
