<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOwnerAssociationInvoice extends ViewRecord
{
    protected static string $resource = OwnerAssociationInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\EditAction::make(),
        ];
    }
}
