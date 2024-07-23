<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class InvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Invoice';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 11;

    protected function getData(): array
    {
        $approvedInvoice = Invoice::where('owner_association_id',Filament::getTenant()->id)->where('status','approved')->count();
        $pendingInvoice = Invoice::where('owner_association_id',Filament::getTenant()->id)->where('status','pending')->count();
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
