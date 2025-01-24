<?php
namespace App\Filament\Resources\ComplaintsenquiryResource\Pages;

use App\Filament\Resources\ComplaintsenquiryResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintsenquiries extends ListRecords
{
    protected static string $resource = ComplaintsenquiryResource::class;
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
            return parent::getTableQuery()->where('complaint_type', 'enquiries')
                ->whereIn('flat_id', $pmFlats)
                ->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        if ($role == 'OA') {
            return parent::getTableQuery()->where('complaint_type', 'enquiries')
                ->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        return parent::getTableQuery()->where('complaint_type', 'enquiries')
            ->whereIn('building_id', $authOaBuildings);

    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
