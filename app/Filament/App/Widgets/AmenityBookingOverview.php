<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FacilityBooking;

class AmenityBookingOverview extends ChartWidget
{
    protected static ?string $heading = 'Amenity Booking Statistics';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';
    protected function getData(): array
    {
        $buildings = DB::table('building_owner_association')->where('owner_association_id', auth()->user()->owner_association_id)
            ->pluck('building_id');
        $bookings = FacilityBooking::where('bookable_type', 'App\Models\Master\Facility')
            ->whereIn('building_id',$buildings)
            ->select(
                DB::raw('SUM(CASE WHEN approved = true THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN approved = false THEN 1 ELSE 0 END) as rejected_count')
            )
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Booking Status',
                    'data' => [$bookings->approved_count, $bookings->rejected_count],
                    'backgroundColor' => ['#10B981', '#EF4444'],
                ]
            ],
            'labels' => ['Approved', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
