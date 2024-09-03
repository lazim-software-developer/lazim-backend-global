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
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        // Get the start date and end date from filters
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfYear()->format('Y-m-d');

        // Convert startDate and endDate to Carbon instances
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfMonth();

        // Determine the total number of months between start date and end date
        $totalMonths = $startDate->diffInMonths($endDate) + 1;

        // Initialize an array to hold the registration count for each month
        $monthlyRegistrations = array_fill(0, $totalMonths, 0);

        // Create an array of month labels between start date and end date
        $monthLabels = [];
        for ($i = 0; $i < $totalMonths; $i++) {
            $monthLabels[] = $startDate->copy()->addMonths($i)->format('F');
        }

        // Start building the query
        $vendorQuery = Vendor::whereBetween('created_at', [$startDate, $endDate])
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->groupBy('year', 'month');

        // Apply building filter if selected
        $buildingId = $this->filters['building'] ?? null;
        if ($buildingId) {
            $vendorQuery->whereHas('buildingvendor', function ($query) use ($buildingId) {
                $query->where('building_id', $buildingId);
            });
        }

        // Fetch vendor registrations grouped by month and year
        $vendors = $vendorQuery->get();

        // Populate the $monthlyRegistrations array with the count of registrations per month
        foreach ($vendors as $vendor) {
            $monthIndex = Carbon::create($vendor->year, $vendor->month)->diffInMonths($startDate);
            if (isset($monthlyRegistrations[$monthIndex])) {
                $monthlyRegistrations[$monthIndex] = $vendor->count;
            }
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
            'labels' => $monthLabels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
