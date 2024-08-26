<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOacomplaintReports extends ListRecords
{
    protected static string $resource = OacomplaintReportsResource::class;
    protected function getTableQuery(): Builder
    {
        $buildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id')->toArray();

        $query = parent::getTableQuery()->where('complaint_type', 'oa_complaint_report');

        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            $query->whereIn('building_id', $buildings);
        }

        return $query;

    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

}
