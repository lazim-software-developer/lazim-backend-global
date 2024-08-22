<?php

namespace App\Filament\Widgets;

use App\Models\Forms\Guest;
use App\Models\Master\Role;
use App\Models\Forms\SaleNOC;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use Filament\Widgets\ChartWidget;

class FormsChart extends ChartWidget
{
    protected static ?string $heading = 'Resident Request Forms';
    protected static ?string $maxHeight = '300px';  // Adjusted height for better visibility
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 6;

    protected function getData(): array
    {
        $user = auth()->user();
        $query = $user->role->name == 'Admin'
            ? function ($query) { return $query; }  // For Admin: no additional conditions
            : function ($query) use ($user) {
                return $query->where('owner_association_id', $user->owner_association_id);
            };  // For non-Admin: filter by owner_association_id

        // Fetch counts
        $data = [
            'AccessCard' => AccessCard::count(),
            'SaleNOC' => SaleNOC::count(),
            'GuestRegistration' => Guest::count(),
            'MovingIn' => MoveInOut::where('type', 'move-in')->count(),
            'MovingOut' => MoveInOut::where('type', 'move-out')->count(),
            'FitOut' => FitOutForm::count(),
            'Residential' => ResidentialForm::count(),
        ];


        return [
                'labels' => array_keys($data),
                'datasets' => [
                    [
                        'data' => array_values($data),
                        'backgroundColor' => [
                            '#FF6384', // Pink
                            '#36A2EB', // Blue
                            '#FFCE56', // Yellow
                            '#4BC0C0', // Teal
                            '#9966FF', // Purple
                            '#FF9F40', // Orange
                            '#FF66CC', // Light Pink
                        ],
                        'borderColor' => '#ffffff',
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

            ],
        ];
    }
}
