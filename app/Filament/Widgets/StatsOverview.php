<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $complaints = Complaint::where('owner_association_id', auth()->user()->owner_association_id);   
        return [
            Stat::make('enquiries', $complaints->where('complaint_type','enquiries')->count())
            ->descriptionIcon('heroicon-s-user-group')
            ->chart([60, 92, 33, 80, 31, 98, 70])
            ->color('success'),
        ];
    }
}
