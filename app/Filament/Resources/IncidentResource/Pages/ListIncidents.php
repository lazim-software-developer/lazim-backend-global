<?php

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use App\Models\Master\Role;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIncidents extends ListRecords
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (auth()->user()->role->name == 'Property Manager') {
            $buildings = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()?->owner_association_id)
                ->where('active', true)
                ->pluck('building_id');

            return parent::getTableQuery()
                ->where('complaint_type', '=', 'incident')
                ->whereIn('building_id', $buildings)
                ->latest();
        }
        return parent::getTableQuery()->where('complaint_type', 'incident')->latest();
    }
}
