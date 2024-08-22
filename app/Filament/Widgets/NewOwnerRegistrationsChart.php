<?php
namespace App\Filament\Widgets;

use App\Models\User\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class NewOwnerRegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 6;

    protected static ?string $heading = 'New Owner Registrations';
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        // Get registrations for the last 12 months
        $registrations = User::select(
                DB::raw('DATE_FORMAT(created_at, "%b") as period'),  // Abbreviated month names
                DB::raw('COUNT(*) as count')
            )
            ->whereHas('role', function ($query) {
                $query->where('name', 'Owner');
            })
            ->where('created_at', '>=', now()->subYear())  // Limit to last 12 months
            ->groupBy('period')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->pluck('count', 'period')
            ->toArray();

        return [
            'labels' => array_keys($registrations),
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => array_values($registrations),
                    'borderColor' => '#4CAF50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,  // Smooth the line
                ],
            ],
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
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 5,  // Set vertical range after a specific interval
                    ],
                    'grid' => [
                        'borderDash' => [5, 5],  // Dashed grid lines for y-axis
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,  // Hide grid lines for x-axis
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            return $context['dataset']['label'] . ': ' . $context['formattedValue'] . ' registrations';
                        },
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}
