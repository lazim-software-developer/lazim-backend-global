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
            Actions\EditAction::make(),
        ];
    }
}
