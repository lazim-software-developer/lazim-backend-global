<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Invoice;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ContractsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $vendorIds = Vendor::all()->pluck('id');
            $contracts = Contract::query()->whereIn('vendor_id', $vendorIds);
            $Invoices = Invoice::whereIn('vendor_id', $vendorIds);
            return [
                Stat::make('Contracts', $contracts->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
                Stat::make('Invoices', $Invoices->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
            ];
        } else {
            $vendorIds = Vendor::all()->where('owner_association_id', auth()->user()->owner_association_id)->pluck('id');
            $contracts = Contract::query()->whereIn('vendor_id', $vendorIds);
            $Invoices = Invoice::whereIn('vendor_id', $vendorIds);
            return [
                Stat::make('Contracts', $contracts->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
                Stat::make('Invoices', $Invoices->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
            ];
        }
    }
}
