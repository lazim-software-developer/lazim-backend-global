<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Building\Complaint;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Facility;
use App\Models\User\User;
use App\Models\UserApproval;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class FirstTileGroup extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;
    protected $role;
    protected $listeners = ['filtersUpdated' => 'applyFilters'];
    protected int | string | array $columnSpan = 12;

    public $buildingId;
    public $startDate;
    public $endDate;

    protected function getStats(): array
    {
        $this->buildingId = $this->filters['building_id'] ?? null;
        $this->role = Role::where('id', auth()->user()->role_id)->first()->name;



        return [ $this->getTotalOwners(),
                $this->getPendingResidentApprovals(),
                $this->getOpenComplaints(),
                $this->getPendingFacilityBooking()
        ];
    }

    private function getTotalOwners(){
        // Base query for vendors and invoices
        $ownerQuery = User::whereHas('role', function ($query) {
            $query->where('name', 'Owner');
        });

        // Filter by building if a building is selected
        if ($this->buildingId) {
            $ownerQuery->whereHas('role', function ($q) {
                $q->where('building_id', $this->buildingId);
            });
        }

        if ($this->role != 'Admin') {
            $ownerQuery->where('owner_association_id',  auth()->user()->owner_association_id);
        }

        $this->applyBuildingAndRoleFilters($ownerQuery);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $ownerQuery);

        return Stat::make('Total Owners', $ownerQuery->count())
        ->icon('heroicon-s-user-group')
        ->chart(array_values($chartData))
        ->color('info');
    }

    private function getPendingResidentApprovals(){
        // Count pending approvals
        $pendingApprovalQuery = UserApproval::where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', '');
        });

        // Filter by building if a building is selected
        if ($this->buildingId) {
            $pendingApprovalQuery->whereHas('user', function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->where('building_id', $this->buildingId);
                });
            });
        }

        if ($this->role != 'Admin') {
            $pendingApprovalQuery->whereHas('user', function ($query) {
                $query->where('owner_association_id',  auth()->user()->owner_association_id);
            });
        }

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $pendingApprovalQuery);

        return Stat::make('Pending Resident Approvals', $pendingApprovalQuery->count())
        ->icon('heroicon-s-user-add')
        ->chart(array_values($chartData))
        ->color('danger');
    }

    private function getOpenComplaints() {
        // Count open complaints
        $openComplaintsQuery = Complaint::where('status', 'open');

        $this->applyBuildingAndRoleFilters($openComplaintsQuery);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $openComplaintsQuery);

        // Return the Stat widget
        return Stat::make('Open Complaints', $openComplaintsQuery->count())
            ->icon('heroicon-s-exclamation-circle')
            ->chart(array_values($chartData))
            ->color('danger'); // Color to represent open complaints
    }

    private function getPendingFacilityBooking() {
        // Count pending facility bookings
        $pendingFacilityBookingQuery = FacilityBooking::where('approved', 0)->where('bookable_type',Facility::class);

        $this->applyBuildingAndRoleFilters($pendingFacilityBookingQuery);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $pendingFacilityBookingQuery);

        // Return the Stat widget
        return Stat::make('Pending Facility Bookings', $pendingFacilityBookingQuery->count())
            ->icon('heroicon-s-clock')
            ->chart(array_values($chartData))
            ->color('warning'); // Color to represent pending bookings
    }

    private function getMonthlyChartData($query): array
    {
        return $query->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [now()->subMonths(4), now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->pad(4, 0) // Ensures at least 4 months of data
            ->values()
            ->toArray();
    }

    private function applyBuildingAndRoleFilters($query)
    {
        if ($this->buildingId) {
            $query->where('building_id', $this->buildingId);
        }

        if ($this->role != 'Admin') {
            $query->where('owner_association_id', auth()->user()->owner_association_id);
        }
    }

}
