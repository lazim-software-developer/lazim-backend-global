<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOwnerAssociationInvoices extends ListRecords
{
    protected static string $resource = OwnerAssociationInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
           Action::make('Generate Invoice')->url(function() {
                if (in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])) {
                    return '/app/generate-invoice';
                } else {
                    return '/admin/generate-invoice';
                }
            })

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
