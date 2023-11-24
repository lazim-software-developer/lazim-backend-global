<?php

namespace App\Filament\Resources\OAMInvoiceResource\Pages;

use App\Filament\Resources\OAMInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOAMInvoices extends ListRecords
{
    protected static string $resource = OAMInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
