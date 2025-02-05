<?php

namespace App\Filament\Resources\SnagsResource\Pages;

use App\Models\OwnerAssociation;
use DB;
use Filament\Actions;
use App\Filament\Resources\SnagsResource;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSnags extends ListRecords
{
    protected static string $resource = SnagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Snag'),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin')
        {
            return parent::getTableQuery()->where('complaint_type', 'snag');
        }
        elseif (auth()->user()->role->name == 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
            ->pluck('role')[0] == 'Property Manager') {
    $buildings = DB::table('building_owner_association')
        ->where('owner_association_id', auth()->user()?->owner_association_id)
        ->where('active', true)
        ->pluck('building_id');

    return parent::getTableQuery()
        ->where('complaint_type', '=', 'snag')
        ->whereIn('building_id', $buildings)
        ->latest();
}

        return parent::getTableQuery()->where('complaint_type', 'snag')->where('owner_association_id',auth()->user()?->owner_association_id);
    }
}
