<?php

namespace App\Filament\Resources\GuestRegistrationResource\Pages;

use App\Filament\Resources\GuestRegistrationResource;
use DB;
use Filament\Actions;
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
        $flatVisitorIds = DB::table('flat_visitors')
            ->whereIn('building_id', $pmBuildings)
            ->where('type', 'guest')
            ->pluck('id');

        return auth()->user()->role->name == 'Admin' ? parent::getTableQuery() : parent::getTableQuery()->whereIn('flat_visitor_id',$flatVisitorIds);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
