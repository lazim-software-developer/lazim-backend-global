<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\CoolingAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $overdueBTUCount = Bill::where('type', 'BTU')
            ->where('status', 'Overdue')
            ->count();

        $overdueCoolingCount = CoolingAccount::where('status', 'overdue')
            ->count();

        return [
            Stat::make('Overdue Cooling Accounts', $overdueCoolingCount)
                ->description('Total overdue cooling accounts')
                ->color('danger')
                ->url('/app/cooling-accounts?tableFilters[status][status]=overdue')
                ->color('blue')
                ->chart([12, 22, 32, 42, 52])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E0F2FF, #90CDF4); color: #1D4ED8;']),
        ];
    }
}
