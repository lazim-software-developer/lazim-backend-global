<?php
namespace App\Filament\Resources\ComplaintssuggessionResource\Pages;

use App\Filament\Resources\ComplaintssuggessionResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintssuggessions extends ListRecords
{
    protected static string $resource = ComplaintssuggessionResource::class;
    protected function getTableQuery(): Builder
    {
        $role    = auth()->user()->role->name;
        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        $authOaBuildings = Building::where('owner_association_id', auth()->user()?->owner_association_id)
            ->pluck('id')->toArray();

        if ($role == 'Admin') {
            return parent::getTableQuery();
        }
        if ($role == 'Property Manager') {
            return parent::getTableQuery()->where('complaint_type', 'suggestions')
                ->whereIn('flat_id', $pmFlats)
                ->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        if ($role == 'OA') {
            return parent::getTableQuery()->where('complaint_type', 'suggestions')
                ->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        return parent::getTableQuery()->where('complaint_type', 'suggestions')
            ->whereIn('building_id', $authOaBuildings);

    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
