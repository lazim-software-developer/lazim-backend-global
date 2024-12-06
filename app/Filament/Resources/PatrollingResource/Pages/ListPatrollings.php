<?php

namespace App\Filament\Resources\PatrollingResource\Pages;

use App\Filament\Resources\PatrollingResource;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPatrollings extends ListRecords
{
    protected static string $resource = PatrollingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'){
            $buildings = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)
                ->pluck('building_id');

            return parent::getTableQuery()
                ->whereIn('building_id', $buildings)
                ->latest();
        }
        return parent::getTableQuery()->where('owner_association_id',Filament::getTenant()->id);
    }
}
