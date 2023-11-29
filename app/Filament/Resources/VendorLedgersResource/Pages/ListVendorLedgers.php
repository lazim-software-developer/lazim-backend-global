<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use App\Filament\Resources\VendorLedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorLedgers extends ListRecords
{
    protected static string $resource = VendorLedgersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
