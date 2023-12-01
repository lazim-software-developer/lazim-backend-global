<?php

namespace App\Filament\Widgets;

use App\Models\User\User;
use Filament\Widgets\ChartWidget;

class RegistrationChart extends ChartWidget
{
    protected static ?string $heading = 'Registration';
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $vendors = User::where('role_id',2)->count();
        $residents = User::whereIn('role_id',[1,11] )->count();
        return [
            'datasets' => [
                [
                    'label' => ['Vendors', 'Residents'],
                    'data' => [$vendors, $residents],
                    'backgroundColor' => ['#1d5ee0', '#f2360c'],
                    'borderColor' => '#000000',
                ],
            ],
            'labels' => ['Vendors', 'Residents'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
