<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\Invoice;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class PaymentChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 11;
    protected static ?string $heading = 'Payments';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Invoice::query()->where('owner_association_id', Filament::getTenant()->id)->where('status','approved');

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $query->where('date', '>=', $startOfDay);
        }
        
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
            $query->where('date', '<=', $endOfDay);
        }

        $partiallyPaid = clone $query;
        $fullyPaid = clone $query;
        $unPaid = clone $query;
        // Log::info($partiallyPaid->toSql());


        $partiallyPaidCount = $partiallyPaid->where('balance', '!=', 0)->where('balance', '!=', 'invoice_amount')->count();        
        $fullyPaidCount = $fullyPaid->where('balance', '=', 0)->count();        
        $unpaidCount = $unPaid->where('balance', '=', 'invoice_amount')->count(); 

        return [
            'datasets' => [
                    [
                        'label' => 'Partially Paid',
                        'data' => [$partiallyPaidCount],
                        'backgroundColor' => '#f5fa5a',
                        'borderColor' => '#ffffff',
                    ],
                    [
                        'label' => 'Fully Paid',
                        'data' => [$fullyPaidCount],
                        'backgroundColor' => '#bbf76d',
                        'borderColor' => '#ffffff',
                    ],
                    [
                        'label' => 'UnPaid',
                        'data' => [$unpaidCount],
                        'backgroundColor' => '#fa5a5a',
                        'borderColor' => '#ffffff',
                    ]
                ],
                'labels' => [''],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
