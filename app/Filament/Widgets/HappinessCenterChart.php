<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use Filament\Widgets\ChartWidget;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\Log;

class HappinessCenterChart extends ChartWidget
{
    protected static ?string $heading = 'Happiness Center';
    protected static ?string $maxHeight = '200px';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $complaints = Complaint::count();
            $enquiries = Complaint::where('complaint_type', 'enquiries')->count();
            $suggestions = Complaint::where('complaint_type', 'suggestions')->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Suggestions', 'Enquiries'],
                        'data' => [$suggestions, $enquiries],
                        'backgroundColor' => ['#f5fa5a', '#5a82fa'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Suggestions', 'Enquiries'],
            ];
        } else {
            $complaints = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->count();
            $enquiries = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->where('complaint_type', 'enquiries')->count();
            $suggestions = Complaint::where('owner_association_id', auth()->user()->owner_association_id)->where('complaint_type', 'suggestions')->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Suggestions', 'Enquiries'],
                        'data' => [$suggestions, $enquiries],
                        'backgroundColor' => ['#f5fa5a', '#5a82fa'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Suggestions', 'Enquiries'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
