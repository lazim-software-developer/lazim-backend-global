<?php
namespace App\Filament\Resources\FacilityBookingResource\Pages;

use App\Filament\Resources\FacilityBookingResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListFacilityBookings extends ListRecords
{
    protected static string $resource = FacilityBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id')->toArray();

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit');
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'
            || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
            ->pluck('role')[0] == 'Property Manager') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')->whereIn('flat_id', $pmFlats);
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
            return parent::getTableQuery()
                ->where('bookable_type', 'App\Models\WorkPermit')
                ->whereIn('building_id', $buildings);
        }

        return parent::getTableQuery()
            ->where('bookable_type', 'App\Models\WorkPermit');
    }
}
