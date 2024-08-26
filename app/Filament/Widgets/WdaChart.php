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
    // protected static ?string $maxWidth = '100%';
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = WDA::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        $vendors = Vendor::where('owner_association_id', Filament::getTenant()->id)->get();

        $vendorNames = [];
        $approvedCounts = [];
        $pendingCounts = [];

        foreach ($vendors as $vendor) {
            $vendorNames[] = $vendor->name;

            $approvedCount = $query->clone()
                ->where('vendor_id', $vendor->id)
                ->where('status', 'approved')
                ->count();
            $pendingCount = $query->clone()
                ->where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->count();

            $approvedCounts[] = $approvedCount;
            $pendingCounts[] = $pendingCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => $approvedCounts,
                    'backgroundColor' => '#4DB6AC',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status', // Enable stacking
                ],
                [
                    'label' => 'Pending',
                    'data' => $pendingCounts,
                    'backgroundColor' => '#fd7e14',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status', // Enable stacking
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
