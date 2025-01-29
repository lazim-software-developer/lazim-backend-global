<?php
namespace App\Filament\Resources\GuestRegistrationResource\Pages;

use App\Filament\Resources\GuestRegistrationResource;
use App\Models\OwnerAssociation;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGuestRegistrations extends ListRecords
{
    protected static string $resource = GuestRegistrationResource::class;
    // protected static ?string $title = 'Guests';
    protected function getTableQuery(): Builder
    {
        $pmBuildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');
        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        $flatVisitorIds = DB::table('flat_visitors')
            ->whereIn('building_id', $pmBuildings)
            ->where('type', 'guest')
            ->pluck('id');
        $pmFlatVisitors = DB::table('flat_visitors')
            ->whereIn('flat_id', $pmFlats)
            ->where('type', 'guest')
            ->pluck('id');

        if (auth()->user()->role->name == 'Admin') {
            return parent::getTableQuery();
        }

        if (auth()->user()->role->name == 'Property Manager'
            || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
            ->pluck('role')[0] == 'Property Manager') {
            return parent::getTableQuery()->whereIn('flat_visitor_id', $pmFlatVisitors);
        } elseif (auth()->user()->role->name == 'OA') {
            return parent::getTableQuery()->whereIn('flat_visitor_id', $flatVisitorIds);
        }
        return parent::getTableQuery();
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
