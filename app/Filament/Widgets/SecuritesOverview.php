<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SecuritesOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $securityRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Security')->pluck('id');
        $securityCount = User::where('role_id',$securityRoleId)->count();

        $vendorsCount = Vendor::where('owner_association_id',Filament::getTenant()->id)->count();

        $technicianRoleId = Role::where('owner_association_id',Filament::getTenant()->id)->where('name','Technician')->pluck('id');
        $technicianCount = User::where('role_id',$technicianRoleId)->count();


        return [
            Stat::make('Total Securities',$securityCount)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('info'),

            Stat::make('Total Vendors',$vendorsCount)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('success'),

            Stat::make('Total Technicians',$technicianCount)
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('primary'),
        ];
    }
}
