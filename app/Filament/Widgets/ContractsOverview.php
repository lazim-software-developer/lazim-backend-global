<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Log;

class ContractsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Contract::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $query->where('start_date', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
            $query->where('end_date', '<=', $endOfDay);
        }
        

        $contractCount = $query->count();

            
            return [
                Stat::make('Contracts', $contractCount)
                    ->descriptionIcon('heroicon-s-user-group')
                    ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
            ];
        
    }
}
