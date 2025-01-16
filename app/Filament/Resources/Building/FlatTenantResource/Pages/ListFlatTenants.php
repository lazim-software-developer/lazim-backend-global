<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\UserApproval;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title   = 'Residents';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        $buildingIds = Building::where('owner_association_id', $user?->owner_association_id)->pluck('id')->toArray();

        $userRoleName = Role::where('id', $user->role_id)->value('name');

        $approvedTenants = UserApproval::where('status', 'Approved')->pluck('user_id')->toArray();

        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        if (auth()->user()?->role?->name === 'Property Manager') {
            return parent::getTableQuery()->whereIn('building_id', $pmbuildingIds)
                ->whereIn('tenant_id', $approvedTenants);
        }

        if ($userRoleName == 'Admin') {
            return parent::getTableQuery(); // Full query for Property Manager/Admin
        }

        return parent::getTableQuery()->whereIn('building_id', $pmbuildingIds);
    }
}
