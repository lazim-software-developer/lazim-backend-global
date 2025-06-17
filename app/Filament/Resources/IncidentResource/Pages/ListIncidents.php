<?php
namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use App\Models\Building\Building;
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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $authOaBuildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)
            ->pluck('id');

        $buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        if (in_array(auth()->user()->role->name, ['Property Manager', 'OA'])) {
            return parent::getTableQuery()
                ->where('complaint_type', '=', 'incident')
                ->whereIn('building_id', $buildings)
                ->latest();
        }
        if (auth()->user()->role->name === 'Admin') {
            return parent::getTableQuery()
                ->where('complaint_type', '=', 'incident')
                ->latest();
        }
        return parent::getTableQuery()->where('complaint_type', 'incident')
            ->whereIn('building_id', $authOaBuildings)->latest();
    }
}
