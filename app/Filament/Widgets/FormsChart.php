<?php

namespace App\Filament\Widgets;

use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\Guest;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\SaleNOC;
use App\Models\ResidentialForm;
use Filament\Widgets\ChartWidget;

class FormsChart extends ChartWidget
{
    protected static ?string $heading = 'Forms';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $saleNOC = SaleNOC::where('owner_association_id', auth()->user()->owner_association_id)->count();
        $accessCard = AccessCard::where('owner_association_id', auth()->user()->owner_association_id)->count();
        $guests = Guest::where('owner_association_id', auth()->user()->owner_association_id)->count();
        $moveIn = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type','move-in')->count();
        $moveOut = MoveInOut::where('owner_association_id', auth()->user()->owner_association_id)->where('type','move-out')->count();
        $fitOut = FitOutForm::where('owner_association_id', auth()->user()->owner_association_id)->count();
        $residential = ResidentialForm::where('owner_association_id', auth()->user()->owner_association_id)->count();
        return [
            'datasets' => [
                [
                    'label' => 'AccessCard',
                    'data' => [$accessCard],
                    'backgroundColor' => '#f5fa5a',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'SaleNOC',
                    'data' => [$saleNOC],
                    'backgroundColor' => '#5a82fa',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'GuestRegistration',
                    'data' => [$guests],
                    'backgroundColor' => '#5afaa7',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'MovingIn',
                    'data' => [$moveIn],
                    'backgroundColor' => '#fa5a92',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'MovingOut',
                    'data' => [$moveOut],
                    'backgroundColor' => '#fa5a5a',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'FitOut',
                    'data' => [$fitOut],
                    'backgroundColor' => '#fa9a5a',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'Residential',
                    'data' => [$residential],
                    'backgroundColor' => '#bbf76d',
                    'borderColor' => '#ffffff',
                ]
            ],
            'labels' => ['Forms'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
