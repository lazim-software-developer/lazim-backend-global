<?php

namespace App\Filament\Widgets;

use App\Models\Forms\MoveInOut;
use App\Models\User\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MoveInOutChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading   = 'Move In and Out Requests';
    protected static ?string $maxHeight = '400px';
    protected static ?string $maxWidth  = '50%';

    // protected static ?string $maxWidth = '100%';
    protected static ?int $sort = 4;

    // protected function getColumns(): int
    // {
    //     return 2;
    // }

    public static function canView(): bool
    {
        $user = User::find(auth()->user()->id);
        return ($user->can('view_any_move::in::forms::document') || $user->can('view_any_move::out::forms::document'));
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $endDate   = $this->filters['endDate'] ?? Carbon::now()->endOfYear()->format('Y-m-d');

        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m-d', $endDate)->endOfMonth();

        $totalMonths = $startDate->diffInMonths($endDate) + 1;

        $months        = [];
        $moveInCounts  = [];
        $moveOutCounts = [];

        for ($i = 0; $i < $totalMonths; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $monthName    = $currentMonth->format('M');
            $months[]     = $monthName;

            // Count move-ins created in this month
            $moveInCount = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)
                ->where('type', 'move-in')
                ->whereYear('created_at', $currentMonth->year)
                ->whereMonth('created_at', $currentMonth->month)
                ->when($this->filters['building'] ?? null, function ($query) {
                    return $query->where('building_id', $this->filters['building']);
                })
                ->count();
            $moveInCounts[] = $moveInCount;

            // Count move-outs created in this month
            $moveOutCount = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)
                ->where('type', 'move-out')
                ->whereYear('created_at', $currentMonth->year)
                ->whereMonth('created_at', $currentMonth->month)
                ->when($this->filters['building'] ?? null, function ($query) {
                    return $query->where('building_id', $this->filters['building']);
                })
                ->count();
            $moveOutCounts[] = $moveOutCount;
        }

        return [
            'datasets' => [
                [
                    'label'       => 'Move In',
                    'data'        => $moveInCounts,
                    'borderColor' => '#4DB6AC',
                    'borderWidth' => 2,
                    'fill'        => false,
                ],
                [
                    'label'       => 'Move Out',
                    'data'        => $moveOutCounts,
                    'borderColor' => '#fd7e14',
                    'borderWidth' => 2,
                    'fill'        => false,
                ],
            ],
            'labels'   => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales'  => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Month',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title'       => [
                        'display' => true,
                        'text'    => 'Number of Requests',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
