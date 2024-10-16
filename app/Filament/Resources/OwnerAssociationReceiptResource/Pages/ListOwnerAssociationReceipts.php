<?php

namespace App\Filament\Resources\OwnerAssociationReceiptResource\Pages;

use App\Filament\Resources\OwnerAssociationReceiptResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
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
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery();
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            $buildingIds = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->pluck('building_id');

            return parent::getTableQuery()->whereIn('building_id', $buildingIds);
        }

        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()->id);
    }

}
