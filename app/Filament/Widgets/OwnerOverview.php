<?php
 
namespace App\Filament\Widgets;

use App\Models\Building\Flat;
use App\Models\FlatOwners;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class OwnerOverview extends BaseWidget
{
    protected static ?int $sort = 2;
 
    protected function getStats(): array
    {
        // $ownerRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Owner')->pluck('id');
        // $tenantRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Tenant')->pluck('id');
        // $totalOwners = User::where('role_id',$ownerRoleId)->count();
        // $totalTenants = User::where('role_id',$tenantRoleId)->count();

        $flats = Flat::where('owner_association_id', Filament::getTenant()->id)->pluck('id');
        $owners = FlatOwners::whereIn('flat_id',$flats)->distinct('flat_id')->count();
        // dd($flats);

        $tenants = MollakTenant::where('owner_association_id', Filament::getTenant()->id)->distinct('email')->count();

        return [
            Stat::make('Owners',$owners)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('info'),
 
            Stat::make('Tenants',$tenants)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->Color('success')
        ];
    }
}