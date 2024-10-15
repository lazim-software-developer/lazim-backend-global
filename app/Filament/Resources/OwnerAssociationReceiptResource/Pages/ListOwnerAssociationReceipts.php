<?php

namespace App\Filament\Resources\OwnerAssociationReceiptResource\Pages;

use App\Filament\Resources\OwnerAssociationReceiptResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOwnerAssociationReceipts extends ListRecords
{
    protected static string $resource = OwnerAssociationReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('Generate Receipt')->url(function () {
                if (in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])) {
                    return '/app/generate-receipt';
                }
                else {
                    return '/admin/generate-receipt';
                }

            }),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if (in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])) {
            return parent::getTableQuery();
        }

        return parent::getTableQuery()->where('owner_association_id',Filament::getTenant()->id);
    }
}
