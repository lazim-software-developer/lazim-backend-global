<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\WDA;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class WdaChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'WDA';
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 8;
    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $buildingId = $this->filters['building'] ?? null;

        $query = WDA::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        if ($buildingId) {
            $query->where('building_id', $buildingId);
        }

        $vendors = Vendor::where('owner_association_id', Filament::getTenant()->id)->get();

        $vendorNames = [];
        $approvedCounts = [];
        $pendingCounts = [];

        foreach ($vendors as $vendor) {
            // Count approved and pending WDAs for each vendor
            $approvedCount = (clone $query)
                ->where('vendor_id', $vendor->id)
                ->where('status', 'approved')
                ->count();
            $pendingCount = (clone $query)
                ->where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->count();

            // Only include vendors with at least one approved or pending WDA
            if ($approvedCount > 0 || $pendingCount > 0) {
                $vendorNames[] = $vendor->name;
                $approvedCounts[] = $approvedCount;
                $pendingCounts[] = $pendingCount;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => $approvedCounts,
                    'backgroundColor' => '#4DB6AC',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status',
                ],
                [
                    'label' => 'Pending',
                    'data' => $pendingCounts,
                    'backgroundColor' => '#fd7e14',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status',
                ],
            ],
            'labels' => $vendorNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Set chart type to bar
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true, // Enable stacking for x-axis
                ],
                'y' => [
                    'stacked' => true, // Enable stacking for y-axis
                ],
            ],
        ];
    }
}
