<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Vendor\Vendor;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class VendorChart extends ChartWidget
{
    use InteractsWithPageFilters;
    
    protected static ?string $heading = 'Vendor Registrations';
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        // Get the start date from filters or use current year if no filter is provided
        $startDate = $this->filters['startDate'] ?? null;

        // Determine the year based on the start date or use the current year
        $year = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->year : Carbon::now()->year;

        // Initialize an array to hold the registration count for each month
        $monthlyRegistrations = array_fill(0, 12, 0);

        // Fetch vendor registrations grouped by month for the determined year
        $vendors = Vendor::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        // Populate the $monthlyRegistrations array with the count of registrations per month
        foreach ($vendors as $vendor) {
            $monthlyRegistrations[$vendor->month - 1] = $vendor->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Registered Vendors',
                    'data' => $monthlyRegistrations,
                    'borderColor' => '#007bff', // Soft blue color
                    'backgroundColor' => 'rgba(0, 123, 255, 0.3)', // Light blue with transparency
                ],
            ],
            'labels' => [
                'January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
