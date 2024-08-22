<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ComplaintCategoriesChart extends ChartWidget
{
    protected static ?string $heading = 'Complaints';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 6;

    protected static ?int $sort = 8;
    public function getData(): array
    {
        // Fetch complaint data
        $complaintData = Complaint::select(
            'category',
            DB::raw('COUNT(*) as count')
        )->whereNotNull('category')
            ->groupBy('category')
            ->get();

        $categories = $complaintData->pluck('category');
        $data = $complaintData->pluck('count');

        // Generate a color palette with more colors to accommodate more categories
        $colorPalette = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FFCD56', '#66FF66', '#FF66CC', '#FF6666'];

        return  [
            'labels' => $categories,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colorPalette, 0, count($categories)),
                    'borderColor' => '#fff',
                    'borderWidth' => 1,
                ],
            ],
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
                            'size' => 14,
                        ],
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($tooltipItem) {
                            return $tooltipItem->label . ': ' . $tooltipItem->raw . ' complaints';
                        },
                    ],
                ],
            ],
        ];
    }
}
