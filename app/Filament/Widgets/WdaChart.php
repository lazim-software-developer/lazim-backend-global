<?php
 
namespace App\Filament\Widgets;
 
use App\Models\Accounting\WDA;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class WdaChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'WDA';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 12;
 
    protected function getData(): array
    {
        
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = WDA::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        $approvedWdaQuery = clone $query;
        $pendingWdaQuery = clone $query; 
        
        $approvedWda = $approvedWdaQuery->where('status', 'approved')->count();
        $pendingWda = $pendingWdaQuery->where('status', 'pending')->count();


        // $approvedWda = WDA::where('owner_association_id',Filament::getTenant()->id)->where('status','approved')->count();
        // $pendingWda = WDA::where('owner_association_id',Filament::getTenant()->id)->where('status','pending')->count();

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