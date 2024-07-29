<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use Filament\Widgets\ChartWidget;
use App\Models\Building\Complaint;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class HappinessCenterChart extends ChartWidget
{
    use InteractsWithPageFilters;
    
    protected static ?string $heading = 'Complaints';
    protected static ?string $maxHeight = '200px';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Complaint::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        $HappinessCenterOpen = clone $query;
        $HappinessCenterClosed = clone $query;
        $HelpDeskOpen = clone $query;
        $HelpDeskClosed = clone $query;

        $HappinessCenterOpenCount = $HappinessCenterOpen->where('status','open')->where('complaint_type','tenant_complaint')->count();
        $HappinessCenterClosedCount = $HappinessCenterClosed->where('status','closed')->where('complaint_type','tenant_complaint')->count();

        $HelpDeskOpenCount = $HelpDeskOpen->where('status','open')->where('complaint_type','help_desk')->count();
        $HelpDeskClosedCount = $HelpDeskClosed->where('status','closed')->where('complaint_type','help_desk')->count();

        Log::info(['HCO'.$HappinessCenterOpenCount,'HCC'.$HappinessCenterClosedCount,'HDO'.$HelpDeskOpenCount,'HDC'.$HelpDeskClosedCount]);

        return [
            'datasets' => [
                [
                    'label' => 'Open',
                    'data' => [$HelpDeskOpenCount, $HappinessCenterOpenCount],  // Open counts for Help Desk and Happiness Center
                    'backgroundColor' => ['#f5fa5a'],
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'Closed',
                    'data' => [$HelpDeskClosedCount, $HappinessCenterClosedCount],  // Closed counts for Help Desk and Happiness Center
                    'backgroundColor' => ['#bbf76d'],
                    'borderColor' => '#ffffff',
                ]
            ],
            'labels' => ['Help Desk', 'Happiness Center'],
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}
