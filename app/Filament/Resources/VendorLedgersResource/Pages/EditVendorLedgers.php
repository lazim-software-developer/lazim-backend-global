<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use App\Filament\Resources\VendorLedgersResource;
use App\Models\Accounting\Invoice;
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
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
