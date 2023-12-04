<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\Invoice;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContractsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected function getStats(): array
    {   $vendorIds = Vendor::all()->where('owner_association_id', auth()->user()->owner_association_id)->pluck('id');
        $contracts = Contract::query()->whereIn('vendor_id', $vendorIds)->where('end_date','>=',Carbon::now()->toDateString());
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
