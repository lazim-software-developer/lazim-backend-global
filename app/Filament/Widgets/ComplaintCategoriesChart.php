<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ComplaintCategoriesChart extends ChartWidget
{
    use InteractsWithPageFilters;
    
    protected static ?string $heading = 'Complaints';
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 7;

    public function getData(): array
    {
        // Fetch complaint data
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $buildingId = $this->filters['building'] ?? null;

        $complaintQuery = Complaint::select(
            'category',
            DB::raw('COUNT(*) as count')
        )
            ->where('owner_association_id', Filament::getTenant()->id) // Filter by owner association ID
            ->whereNotNull('category');

        // Apply building filter if selected
        if ($buildingId) {
            $complaintQuery->where('building_id', $buildingId);
        }

        // Apply date filters if provided
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $complaintQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $complaintQuery->where('created_at', '<=', $endOfDay);
        }

        // Group by category and get the results
        $complaintData = $complaintQuery->groupBy('category')->get();

        $categories = $complaintData->pluck('category');
        $data = $complaintData->pluck('count');

        // Generate a color palette with more colors to accommodate more categories
        $colorPalette = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FFCD56', '#66FF66', '#FF66CC', '#FF6666'];

        return  [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colorPalette, 0, count($categories)),
                    'borderColor' => '#fff',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $categories,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
            ],
        ];
    }
}
