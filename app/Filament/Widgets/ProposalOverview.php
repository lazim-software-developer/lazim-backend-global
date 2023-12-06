<?php

namespace App\Filament\Widgets;

use App\Models\Vendor\Vendor;
use App\Models\Accounting\Proposal;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProposalOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        $vendorIds = Vendor::all()->where('owner_association_id', auth()->user()->owner_association_id)->pluck('id')->toArray();
        return [
            Stat::make('Request Proposal', Proposal::query()->whereIn('vendor_id', $vendorIds)->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('info'),
            Stat::make('Approved Proposal', Proposal::query()->whereIn('vendor_id', $vendorIds)->where('status','approved')->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('success'),
            Stat::make('Rejected Proposal', Proposal::query()->whereIn('vendor_id', $vendorIds)->where('status','rejected')->count())
                ->descriptionIcon('heroicon-s-user-group')
                ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('danger'),
        ];
    }
}
