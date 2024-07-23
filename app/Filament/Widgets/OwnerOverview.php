<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OwnerOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $ownerRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Owner')->pluck('id');
        $tenantRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Tenant')->pluck('id');
        $totalOwners = User::where('role_id',$ownerRoleId)->count();
        $totalTenants = User::where('role_id',$tenantRoleId)->count();
        return [
            Stat::make('Owners',$totalOwners)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('info'),

            Stat::make('Tenants',$totalTenants)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->Color('success')
        ];
    }
}
