<?php
namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Filament\Resources\UserApprovalResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use DB;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ListUserApprovals extends ListRecords
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $flats = DB::table('flats')->whereIn('building_id', $pmbuildingIds)->pluck('id')->toArray();

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if(auth()->user()->role->name == 'Admin') {
            return parent::getTableQuery()->latest();
        }
        if (auth()->user()->role->name == 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                ->pluck('role')[0] == 'Property Manager') {
            return parent::getTableQuery()
                ->whereIn('flat_id', $pmFlats)
                ->latest();
        }
        $tenant = Filament::getTenant();
        Log::info($tenant);
        if ($tenant) {
            return parent::getTableQuery()->whereIn('flat_id', $flats)->latest();
        }
        return parent::getTableQuery()->latest();
    }
}
