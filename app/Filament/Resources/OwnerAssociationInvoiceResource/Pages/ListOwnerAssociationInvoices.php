<?php
namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use DB;
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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
           Action::make('Generate Invoice')
           ->label('Generate Invoice')
           ->url(function() {
                if (in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])
                 || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                    ->pluck('role')[0] == 'Property Manager'){
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
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
            ->pluck('role')[0] == 'Property Manager') {
            $buildingIds = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)
                ->pluck('building_id');

            return parent::getTableQuery()->whereIn('building_id', $buildingIds);
        }

        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()->id);
    }

}
