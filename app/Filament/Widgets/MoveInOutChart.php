<?php

namespace App\Filament\Widgets;

use App\Models\Forms\MoveInOut;
use App\Models\User\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MoveInOutChart extends ChartWidget
{
    public ?string $filter = null; // Remove default value to allow null state

    public function mount(): void
    {
        // Convert month number directly to string
        $this->filter = (string) now()->month;
    }

    protected static ?string $heading = 'Move In and Out Requests';
    protected static ?int $sort       = 4;

    protected function getFilters(): ?array
    {
        return [
            '1'  => 'January',
            '2'  => 'February',
            '3'  => 'March',
            '4'  => 'April',
            '5'  => 'May',
            '6'  => 'June',
            '7'  => 'July',
            '8'  => 'August',
            '9'  => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }

    protected function getData(): array
    {
        $selectedMonth = (int) ($this->filter ?? now()->month);
        $currentYear   = now()->year;
        $startDate = Carbon::create($currentYear, $selectedMonth, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Count total move-ins
        $moveInCount = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)
            ->where('type', 'move-in')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($this->filters['building'] ?? null, function ($query) {
                return $query->where('building_id', $this->filters['building']);
            })
            ->count();

        // Count total move-outs
        $moveOutCount = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)
            ->where('type', 'move-out')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($this->filters['building'] ?? null, function ($query) {
                return $query->where('building_id', $this->filters['building']);
            })
            ->count();

        return [
            'datasets' => [
                [
                    'data'            => [$moveInCount, $moveOutCount],
                    'backgroundColor' => ['#4DB6AC', '#fd7e14'],
                ],
            ],
            'labels'   => ['Move In', 'Move Out'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins'             => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
