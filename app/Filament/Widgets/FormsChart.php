<?php

namespace App\Filament\Widgets;

use App\Models\Forms\Guest;
use App\Models\Master\Role;
use App\Models\Forms\SaleNOC;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class FormsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Forms';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        
        // Initialize queries for each model
        $saleNOCQuery = SaleNOC::where('owner_association_id', auth()->user()->owner_association_id);
        $accessCardQuery = AccessCard::where('owner_association_id', auth()->user()->owner_association_id);
        $guestsQuery = Guest::where('owner_association_id', auth()->user()->owner_association_id);
        $fitOutQuery = FitOutForm::where('owner_association_id', auth()->user()->owner_association_id);
        $residentialQuery = ResidentialForm::where('owner_association_id', auth()->user()->owner_association_id);
        $moveInQuery = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type', 'move-in');
        $moveOutQuery = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type', 'move-out');
        $visitorQuery = Visitor::where('owner_association_id', auth()->user()->owner_association_id);
        
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $saleNOCQuery->where('created_at', '>=', $startOfDay);
            $accessCardQuery->where('created_at', '>=', $startOfDay);
            $guestsQuery->where('created_at', '>=', $startOfDay);
            $fitOutQuery->where('created_at', '>=', $startOfDay);
            $residentialQuery->where('created_at', '>=', $startOfDay);
            $moveInQuery->where('created_at', '>=', $startOfDay);
            $moveOutQuery->where('created_at', '>=', $startOfDay);
            $visitorQuery->where('created_at', '>=', $startOfDay);
        }
        
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $saleNOCQuery->where('created_at', '<=', $endOfDay);
            $accessCardQuery->where('created_at', '<=', $endOfDay);
            $guestsQuery->where('created_at', '<=', $endOfDay);
            $fitOutQuery->where('created_at', '<=', $endOfDay);
            $residentialQuery->where('created_at', '<=', $endOfDay);
            $moveInQuery->where('created_at', '<=', $endOfDay);
            $moveOutQuery->where('created_at', '<=', $endOfDay);
            $visitorQuery->where('created_at', '<=', $endOfDay);
        }
        
        $saleNOCCount = $saleNOCQuery->count();
        $accessCardCount = $accessCardQuery->count();
        $guestsCount = $guestsQuery->count();
        $fitOutCount = $fitOutQuery->count();
        $residentialCount = $residentialQuery->count();
        $moveInCount = $moveInQuery->count();
        $moveOutCount = $moveOutQuery->count();
        $visitorCount = $visitorQuery->count();

        return [
            'datasets' => [
                [
                    'label' => 'Forms',
                    'data' => [
                        $accessCardCount, 
                        $saleNOCCount, 
                        $guestsCount, 
                        $moveInCount, 
                        $moveOutCount, 
                        $fitOutCount, 
                        $residentialCount, 
                        $visitorCount
                    ],
                    'backgroundColor' => [
                        '#f5fa5a', 
                        '#5a82fa', 
                        '#5afaa7', 
                        '#fa5a92', 
                        '#fa5a5a', 
                        '#fa9a5a', 
                        '#bbf76d', 
                        '#5a82fa'
                    ],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => [
                'AccessCard', 
                'SaleNOC', 
                'GuestRegistration', 
                'MovingIn', 
                'MovingOut', 
                'FitOut', 
                'Residential', 
                'Visitors'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
