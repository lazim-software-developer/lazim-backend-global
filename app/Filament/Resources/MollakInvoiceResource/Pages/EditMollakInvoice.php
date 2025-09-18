<?php

namespace App\Filament\Resources\MollakInvoiceResource\Pages;

use App\Filament\Resources\MollakInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMollakInvoice extends EditRecord
{
    protected static string $resource = MollakInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
