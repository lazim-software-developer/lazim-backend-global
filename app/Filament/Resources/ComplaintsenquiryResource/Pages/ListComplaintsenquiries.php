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
        $oa_buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->pluck('building_id')
            ->toArray();

        if ($role == 'Admin') {
            return parent::getTableQuery();
        }
        if ($role == 'Property Manager') {
            return parent::getTableQuery()->where('complaint_type', 'enquiries')
                ->whereIn('flat_id', $pmFlats);
        }
        if ($role == 'OA') {
            return parent::getTableQuery()->where('complaint_type', 'enquiries')->whereIn('building_id', $oa_buildings);
        }
        return parent::getTableQuery()->where('complaint_type', 'enquiries')
            ->whereIn('building_id', $oa_buildings);

    }
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
}
