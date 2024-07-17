<?php

namespace App\Filament\Resources\OwnerAssociationReceiptResource\Pages;

use App\Filament\Resources\OwnerAssociationReceiptResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOwnerAssociationReceipts extends ListRecords
{
    protected static string $resource = OwnerAssociationReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('Generate Receipt')->url(function () {
                if (auth()->user()->role->name == 'Admin') {
                    return '/app/generate-receipt';
                } else {
                    return '/admin/generate-receipt';
                }

            }),
        ];
    }
}
