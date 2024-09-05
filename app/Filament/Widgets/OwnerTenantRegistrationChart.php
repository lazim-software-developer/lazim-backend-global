<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OwnerTenantRegistrationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Owners and Tenants Registrations';
    protected static ?string $maxHeight = '400px';
    // protected static ?string $maxWidth = '100%';
    protected static ?int $sort = 4;

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

    // Initialize arrays for storing counts per month and month labels
    $months = [];
    $ownerCounts = [];
    $tenantCounts = [];

    // Loop through each month between start date and end date
    for ($i = 0; $i < $totalMonths; $i++) {
        // Get the current month in the loop
        $currentMonth = $startDate->copy()->addMonths($i);
        $monthName = $currentMonth->format('M');
        $months[] = $monthName;

        // Count distinct owners created in this month
        $ownerCount = DB::table('flat_tenants')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('role', 'Owner')
            ->whereYear('created_at', $currentMonth->year)
            ->whereMonth('created_at', $currentMonth->month)
            ->when($this->filters['building'] ?? null, function ($query) {
                return $query->where('building_id', $this->filters['building']);
            })
            ->count();
        $ownerCounts[] = $ownerCount;

        // Count distinct tenants created in this month
        $tenantCount = DB::table('flat_tenants')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('role', 'Tenant')
            ->whereYear('created_at', $currentMonth->year)
            ->whereMonth('created_at', $currentMonth->month)
            ->when($this->filters['building'] ?? null, function ($query) {
                return $query->where('building_id', $this->filters['building']);
            })
            ->count();
        $tenantCounts[] = $tenantCount;
    }

    return [
        'datasets' => [
            [
                'label' => 'Owners',
                'data' => $ownerCounts,
                'borderColor' => '#4DB6AC',
                'borderWidth' => 2,
                'fill' => false,
            ],
            [
                'label' => 'Tenants',
                'data' => $tenantCounts,
                'borderColor' => '#fd7e14',
                'borderWidth' => 2,
                'fill' => false,
            ],
        ],
        'labels' => $months,
    ];
}

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Count',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
