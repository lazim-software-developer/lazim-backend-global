<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Accounting\Proposal;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProposalOverview extends BaseWidget
{
    use InteractsWithPageFilters;
        
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        
            $vendorIds = Vendor::all()->where('owner_association_id', auth()->user()->owner_association_id)->pluck('id')->toArray();
            $query = Proposal::query()->whereIn('vendor_id', $vendorIds);

            if ($startDate) {
                $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
                $query->where('submitted_on', '>=', $startOfDay);
            }
            if ($endDate) {
                $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
                $query->where('submitted_on', '<=', $endOfDay);
            }

            $approvedQuery = clone $query;
            $pendingQuery = clone $query;

            $approvedProposal = $approvedQuery->where('status','approved');
            $rejectedProposal = $pendingQuery->where('status','rejected');

            return [
                Stat::make('Request Proposal', Proposal::query()->whereIn('vendor_id', $vendorIds)->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('info'),
                Stat::make('Approved Proposal', $approvedProposal->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('success'),
                Stat::make('Rejected Proposal', $rejectedProposal->count())
                    ->descriptionIcon('heroicon-s-user-group')
                    // ->chart([60, 92, 33, 80, 31, 98, 70])
                    ->color('danger'),
            ];
        
    }
}
