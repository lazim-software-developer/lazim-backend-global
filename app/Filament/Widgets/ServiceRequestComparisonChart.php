<?php
namespace App\Filament\Widgets;

use App\Models\Building\FacilityBooking;
use App\Models\Master\Facility;
use App\Models\Master\Service;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ServiceRequestComparisonChart extends ChartWidget
{
    protected static ?int $sort = 10;

    protected static ?string $heading = 'Facility Vs Service Booking';
    protected static ?string $maxHeight = '200px';
    protected int | string | array $columnSpan = 6;

    protected function getData(): array
    {
        // Retrieve bookings and count them grouped by building and bookable type
        $bookings = FacilityBooking::select('buildings.name as building_name', 'facility_bookings.bookable_type', DB::raw('COUNT(*) as count'))
            ->join('buildings', 'buildings.id', '=', 'facility_bookings.building_id')
            ->where('facility_bookings.approved', 1)
            ->groupBy('building_name', 'bookable_type')
            ->get();

        // Prepare data for the chart
        $data = $bookings->groupBy('building_name')->map(function ($bookingsByBuilding) {
            return [
                'facility' => $bookingsByBuilding->where('bookable_type', Facility::class)->sum('count'),
                'service' => $bookingsByBuilding->where('bookable_type', Service::class)->sum('count'),
            ];
        })->toArray();

        // Generate datasets with improved structure
        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Facilities',
                    'data' => array_column($data, 'facility'),
                    'backgroundColor' => '#4CAF50', // Green color for facilities
                    'stack' => 'services',  // Stack the bars
                ],
                [
                    'label' => 'Services',
                    'data' => array_column($data, 'service'),
                    'backgroundColor' => '#FF9800', // Orange color for services
                    'stack' => 'services',  // Stack the bars
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Building Name',
                        'color' => '#333',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Requests',
                        'color' => '#333',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'borderDash' => [5, 5],
                        'color' => '#ddd',
                    ],
                    'ticks' => [
                        'beginAtZero' => true,
                        'stepSize' => 1, // Set step size to match data
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            return $context['dataset']['label'] . ': ' . $context['formattedValue'] . ' requests';
                        },
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'color' => '#333',
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
            ],
        ];
    }
}
