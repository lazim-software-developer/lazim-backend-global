<?php
 
namespace App\Filament\Widgets;
 
use App\Models\Accounting\Invoice;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class InvoiceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Invoice';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 11;
 
    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Invoice::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        $approvedInvoicesQuery = clone $query;
        $pendingInvoicesQuery = clone $query; 
        
        $approvedInvoice = $approvedInvoicesQuery->where('status', 'approved')->count();
        $pendingInvoice = $pendingInvoicesQuery->where('status', 'pending')->count();
        
        return [
            'datasets'=>[
                [
                    'label'=>['Approved','Pending'],
                    'data'=>[$approvedInvoice,$pendingInvoice],
                    'backgroundColor' => ['#3BDD5B', '#F74A4A'],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels'=>['Approved','Pending']
        ];
    }
 
    protected function getType(): string
    {
        return 'doughnut';
    }
}