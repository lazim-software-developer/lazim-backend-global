<?php

namespace App\Filament\Resources\MollakInvoiceResource\Pages;

use App\Filament\Resources\MollakInvoiceResource;
// use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMollakInvoices extends ListRecords
{
    protected static string $resource = MollakInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
}
