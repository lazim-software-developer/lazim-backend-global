<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Log;

class HappinessCenterChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {   
        $complaints = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->count();
        $enquiries = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->where('complaint_type','enquiries')->count();
        $suggestions = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->where('complaint_type','suggestions')->count();
        return [
            'datasets' => [
                [
                    'label' => ['Total','Suggestions','Enquiries'],
                    'data' => [$complaints,$suggestions,$enquiries],
                    'backgroundColor' => ['#0ef065','#1d5ee0','#f2360c'],
                    'borderColor' => '#000000',
                ],
            ],
            'labels' => ['Total','Suggestions','Enquiries'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
