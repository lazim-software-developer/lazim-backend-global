<?php

namespace App\Filament\Widgets;

use App\Models\OwnerAssociationInvoice;
use App\Models\OwnerAssociationReceipt;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceReciptsGeneration extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1 ;


    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        
        $queryInvoice = OwnerAssociationInvoice::where('owner_association_id', Filament::getTenant()->id);
        $queryReceipt = OwnerAssociationReceipt::where('owner_association_id', Filament::getTenant()->id);
        
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $queryInvoice->where('created_at', '>=', $startOfDay);
            $queryReceipt->where('created_at', '>=', $startOfDay);
        }
        
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $queryInvoice->where('created_at', '<=', $endOfDay);
            $queryReceipt->where('created_at', '<=', $endOfDay);
        }
        
        $totalInvoiceGenerated = $queryInvoice->count();
        $totalReceiptGenerated = $queryReceipt->count();
        
        return [
            Stat::make('Total Invoice Generated', $totalInvoiceGenerated)
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
            Stat::make('Total Receipt Generated', $totalReceiptGenerated)
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
        ];
    }
}
