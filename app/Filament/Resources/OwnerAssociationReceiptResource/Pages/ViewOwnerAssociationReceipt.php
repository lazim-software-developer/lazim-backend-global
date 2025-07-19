<?php

namespace App\Filament\Resources\OwnerAssociationReceiptResource\Pages;

use App\Filament\Resources\OwnerAssociationReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOwnerAssociationReceipt extends ViewRecord
{
    protected static string $resource = OwnerAssociationReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\EditAction::make(),
        ];
    }
}
