<?php

namespace App\Filament\Widgets;

use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Widgets\ChartWidget;

class RegistrationChart extends ChartWidget
{
    protected static ?string $heading = 'Registration';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $vendors = User::where('role_id', 2)->count();
            $residents = User::whereIn('role_id', [1, 11])->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Vendors', 'Residents'],
                        'data' => [$vendors, $residents],
                        'backgroundColor' => ['#fa5a5a', '#fa9a5a'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Vendors', 'Residents'],
            ];
        } else {
            $vendors = User::where('role_id', 2)->where('owner_association_id', auth()->user()->owner_association_id)->count();
            $residents = User::whereIn('role_id', [1, 11])->where('owner_association_id', auth()->user()->owner_association_id)->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Vendors', 'Residents'],
                        'data' => [$vendors, $residents],
                        'backgroundColor' => ['#fa5a5a', '#fa9a5a'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Vendors', 'Residents'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
