<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\WDA;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class WdaChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'WDA';
    protected static ?string $maxHeight = '400px'; // Increase the height of the chart
    protected static ?string $maxWidth = '20px';
    protected static ?int $sort = 4;

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

        $approvedWdaQuery = clone $query;
        $pendingWdaQuery = clone $query; 

        $approvedWda = $approvedWdaQuery->where('status', 'approved')->count();
        $pendingWda = $pendingWdaQuery->where('status', 'pending')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => [$approvedWda],
                    'backgroundColor' => '#007bff',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status', // Enable stacking
                ],
                [
                    'label' => 'Pending',
                    'data' => [$pendingWda],
                    'backgroundColor' => '#fd7e14',
                    'borderColor' => '#ffffff',
                    'stack' => 'Status', // Enable stacking
                ],
            ],
            'labels' => ['WDA Status'],
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
