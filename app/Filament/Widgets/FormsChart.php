<?php

namespace App\Filament\Widgets;

use App\Models\Forms\Guest;
use App\Models\Forms\SaleNOC;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Visitor;
use App\Models\Visitor\FlatVisitor;
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
        $buildingId = $this->filters['building'] ?? null;

        // Initialize queries for each model with optional building filter
        $saleNOCQuery = SaleNOC::where('owner_association_id', auth()->user()->owner_association_id);
        $accessCardQuery = AccessCard::where('owner_association_id', auth()->user()->owner_association_id);
        $guestsQuery = Guest::where('owner_association_id', auth()->user()->owner_association_id);
        $fitOutQuery = FitOutForm::where('owner_association_id', auth()->user()->owner_association_id);
        $residentialQuery = ResidentialForm::where('owner_association_id', auth()->user()->owner_association_id);
        $moveInQuery = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type', 'move-in');
        $moveOutQuery = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type', 'move-out');
        $visitorQuery = FlatVisitor::where('owner_association_id', auth()->user()->owner_association_id);

        if ($buildingId) {
            // Apply building filter to the queries that have direct building_id
            $saleNOCQuery->where('building_id', $buildingId);
            $accessCardQuery->where('building_id', $buildingId);
            $fitOutQuery->where('building_id', $buildingId);
            $residentialQuery->where('building_id', $buildingId);
            $moveInQuery->where('building_id', $buildingId);
            $moveOutQuery->where('building_id', $buildingId);
            $visitorQuery->where('building_id', $buildingId);

            // Apply building filter to the Guests query via a join on FlatVisitor
            $guestsQuery->whereHas('flatVisitor', function($query) use ($buildingId) {
                $query->where('building_id', $buildingId);
            });
        }

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

        $saleNocCount = $saleNOCQuery->count();
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
                        $saleNocCount, 
                        $guestsCount, 
                        $moveInCount, 
                        $moveOutCount, 
                        $fitOutCount, 
                        $residentialCount, 
                        $visitorCount
                    ],
                    'backgroundColor' => [
                        '#5581DD', 
                        '#51CEA4', 
                        '#BB86FC', 
                        '#E49B50', 
                        '#E57373', 
                        '#FFD54F', 
                        '#4DB6AC', 
                        '#90CAF9'
                    ],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => [
                'Access card', 
                'Sale NOC', 
                'Holiday Homes Guest Registration', 
                'Move in', 
                'Move out', 
                'Fitout', 
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
