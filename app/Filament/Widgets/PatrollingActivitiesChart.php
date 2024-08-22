<?php

namespace App\Filament\Widgets;

use App\Models\Gatekeeper\Patrolling;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PatrollingActivitiesChart extends ChartWidget
{
    protected static ?string $heading = 'Patrolling Activities';
    protected static ?string $maxHeight = '200px';
    protected int | string | array $columnSpan = 8;

    protected static ?int $sort = 7;

    // Predefined color palette
    private array $colorPalette = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
    ];

    private array $borderPalette = [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
    ];

    public function getData(): array
    {
        // Fetch patrolling data
        $patrollingData = Patrolling::select(
            'buildings.name as building',
            'floors.floors as floor',
            'users.first_name as first_name',
            DB::raw('COUNT(*) as count')
        )
        ->join('users', 'users.id', '=', 'patrollings.patrolled_by')
        ->join('floors', 'floors.id', '=', 'patrollings.floor_id')
        ->join('buildings', 'buildings.id', '=', 'patrollings.building_id')
        ->groupBy('buildings.name', 'floors.floors', 'users.first_name')
        ->get()
        ->groupBy(function($item) {
            return $item->building . ' - Floor ' . $item->floor;
        });

        $series = [];
        $categories = [];

        foreach ($patrollingData as $category => $data) {
            $categories[] = $category;
            foreach ($data as $row) {
                $series[$row->first_name][] = $row->count;
            }
        }

        return [
            'labels' => $categories,
            'datasets' => array_map(
                fn($key, $values) => [
                    'label' => $key,
                    'data' => $values,
                    'backgroundColor' => $this->colorPalette[array_search($key, array_keys($series)) % count($this->colorPalette)],
                    'borderColor' => $this->borderPalette[array_search($key, array_keys($series)) % count($this->borderPalette)],
                    'borderWidth' => 1,
                ],
                array_keys($series),
                array_values($series)
            ),
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
                    'title' => ['display' => true, 'text' => 'Building - Floor'],
                ],
                'y' => [
                    'stacked' => true,
                    'title' => ['display' => true, 'text' => 'Number of Patrols'],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}
