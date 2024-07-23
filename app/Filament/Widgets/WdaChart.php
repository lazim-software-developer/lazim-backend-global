<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\WDA;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class WdaChart extends ChartWidget
{
    protected static ?string $heading = 'WDA';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 12;

    protected function getData(): array
    {
        $approvedWda = WDA::where('owner_association_id',Filament::getTenant()->id)->where('status','approved')->count();
        $pendingWda = WDA::where('owner_association_id',Filament::getTenant()->id)->where('status','pending')->count();
        return [
            'datasets'=>[
                [
                    'label'=>['Approved','Pending'],
                    'data'=>[$approvedWda,$pendingWda],
                    'backgroundColor'=>['#007bff', '#fd7e14'],
                    'borderColor'=>['#ffffff']
                ]
            ],
            'labels'=>['Approved','Pending']

        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
