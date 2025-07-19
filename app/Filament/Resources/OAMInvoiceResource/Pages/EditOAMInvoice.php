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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
