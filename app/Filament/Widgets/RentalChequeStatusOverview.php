<?php

namespace App\Filament\Widgets;

use App\Models\RentalCheque;
use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RentalChequeStatusOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        $query = RentalCheque::query()
            ->whereHas('rentalDetail.flat', function ($query) use($pmFlats) {
                $query->whereIn('id', $pmFlats);
                });

        $overdueCount = (clone $query)->where('status', 'Overdue')->count();

        $paidCount = (clone $query)->where('status', 'Paid')->count();

        $upcomingCount = (clone $query)->where('status', 'Upcoming')->count();

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
     public static function canView(): bool
    {
        return in_array(auth()->user()->role->name, [
            'Property Manager'
        ]);
    }
}
