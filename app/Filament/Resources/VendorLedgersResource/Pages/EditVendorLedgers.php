<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use App\Filament\Resources\VendorLedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorLedgers extends EditRecord
{
    protected static string $resource = VendorLedgersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
