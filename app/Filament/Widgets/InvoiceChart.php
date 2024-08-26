<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\Invoice;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class InvoiceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Invoice';
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 5;

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
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => [$approvedInvoice],
                    'backgroundColor' => '#2ad84c', 
                    'borderColor' => '#ffffff', 
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pending',
                    'data' => [$pendingInvoice],
                    'backgroundColor' => '#fa5a5a',
                    'borderColor' => '#ffffff', 
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Invoices'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
