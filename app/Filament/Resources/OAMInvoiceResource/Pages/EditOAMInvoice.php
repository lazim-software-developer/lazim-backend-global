<?php

namespace App\Filament\Resources\OAMInvoiceResource\Pages;

use App\Filament\Resources\OAMInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOAMInvoice extends EditRecord
{
    protected static string $resource = OAMInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
