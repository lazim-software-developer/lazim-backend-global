<?php
namespace App\Filament\App\Widgets;

use App\Models\Building\FacilityBooking;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AmenityBookingOverview extends ChartWidget
{
    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = (string) now()->month;
    }

    protected static ?string $heading = 'Amenity Booking Statistics';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');
        $selectedMonth = (int) ($this->filter ?? now()->month);
        $bookings      = FacilityBooking::where('bookable_type', 'App\Models\Master\Facility')
            ->whereIn('building_id', $buildings)
            ->whereMonth('created_at', $selectedMonth)
            ->select(
                DB::raw('SUM(CASE WHEN approved = true THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN approved = false THEN 1 ELSE 0 END) as rejected_count')
            )
            ->first();

        return [
            'datasets' => [
                [
                    'label'           => 'Booking Status',
                    'data'            => [$bookings->approved_count, $bookings->rejected_count],
                    'backgroundColor' => ['#10B981', '#EF4444'],
                ],
            ],
            'labels'   => ['Approved', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins'             => [
                'legend' => [
                    'display'  => true,
                    'cutout'   => '60%',
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
    protected function getFilters(): ?array
    {
        return [
            '1'  => 'January',
            '2'  => 'February',
            '3'  => 'March',
            '4'  => 'April',
            '5'  => 'May',
            '6'  => 'June',
            '7'  => 'July',
            '8'  => 'August',
            '9'  => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }
}
