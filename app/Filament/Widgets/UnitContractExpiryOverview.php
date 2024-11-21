<?php

namespace App\Filament\Widgets;

use App\Models\Building\FlatTenant;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnitContractExpiryOverview extends BaseWidget
{
    // protected ?string $heading = 'Contract Expiry Overview';
    protected string|int|array $columnSpan = 'full';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $today = Carbon::now();
        $query = FlatTenant::query()
            ->whereHas('building', function ($query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            });

        $less100Days = (clone $query)
            ->where('end_date', '<=', $today->copy()->addDays(100))
            ->where('end_date', '>', $today)
            ->where('active', true)
            ->count();

        $less60Days = (clone $query)
            ->where('end_date', '<=', $today->copy()->addDays(60))
            ->where('end_date', '>', $today)
            ->where('active', true)
            ->count();

        $less30Days = (clone $query)
            ->where('end_date', '<=', $today->copy()->addDays(30))
            ->where('end_date', '>', $today)
            ->where('active', true)
            ->count();

        return [
            Stat::make('Contracts Expiring in 100 Days', $less100Days)
                ->description('Units with contracts expiring within 100 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([10, 20, $less100Days + 15, $less100Days])
                ->extraAttributes([
                    'class' => 'ring-1 ring-warning-500/30',
                    'style' => 'background: linear-gradient(135deg, #FFF7E0, #FED7AA);'
                ])
                ->url('/app/contract-expiry-overview?days=100'),

            Stat::make('Contracts Expiring in 60 Days', $less60Days)
                ->description('Units with contracts expiring within 60 days')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->chart([5, 10, $less60Days + 10, $less60Days])
                ->extraAttributes([
                    'class' => 'ring-1 ring-danger-500/30',
                    'style' => 'background: linear-gradient(135deg, #FFF0EA, #FFB4A1);'
                ])
                ->url('/app/contract-expiry-overview?days=60'),

            Stat::make('Contracts Expiring in 30 Days', $less30Days)
                ->description('Units with contracts expiring within 30 days')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([2, 4, $less30Days + 5, $less30Days])
                ->extraAttributes([
                    'class' => 'ring-2 ring-danger-500/50',
                    'style' => 'background: linear-gradient(135deg, #FFE0E0, #FF8080);'
                ])
                ->url('/app/contract-expiry-overview?days=30'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->name, [
            'OA', 'MD', 'Property Manager'
        ]);
    }
}
