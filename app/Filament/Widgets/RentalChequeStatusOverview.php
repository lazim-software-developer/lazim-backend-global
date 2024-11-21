<?php

namespace App\Filament\Widgets;

use App\Models\RentalCheque;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class RentalChequeStatusOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        Log::info('Starting to fetch rental cheque statistics');

        $query = RentalCheque::query()
            ->whereHas('rentalDetail.flat.building', function ($query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            });
        Log::info('Base query created for rental cheques');

        $overdueCount = (clone $query)->where('status', 'Overdue')->count();
        Log::info('Overdue cheques count: ' . $overdueCount);

        $paidCount = (clone $query)->where('status', 'Paid')->count();
        Log::info('Paid cheques count: ' . $paidCount);

        $upcomingCount = (clone $query)->where('status', 'Upcoming')->count();
        Log::info('Upcoming cheques count: ' . $upcomingCount);

        Log::info('Preparing stats array for display');
        return [
            Stat::make('Overdue Cheques', $overdueCount)
                ->description('Total overdue cheques')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->url('/app/rental-cheques?activeTab=Overdue')
                ->chart([7, 4, 6, 8, 5, $overdueCount]),

            Stat::make('Paid Cheques', $paidCount)
                ->description('Total paid cheques')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url('/app/rental-cheques?activeTab=Paid')
                ->chart([2, 4, 6, 8, 7, $paidCount]),

            Stat::make('Upcoming Cheques', $upcomingCount)
                ->description('Total upcoming cheques')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url('/app/rental-cheques?activeTab=Upcoming')
                ->chart([3, 5, 7, 4, 6, $upcomingCount]),
        ];
    }
}
