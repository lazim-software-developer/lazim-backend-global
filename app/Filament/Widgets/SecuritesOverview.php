<?php

namespace App\Filament\Widgets;

use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\UserApproval;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SecuritesOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $buildingId = $this->filters['building'] ?? null;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $role = Role::where('owner_association_id', Filament::getTenant()->id);
    
        $securityCount = BuildingPoc::where('owner_association_id',auth()->user()->owner_association_id)->where('active',true)->count();
        if($buildingId){
            $securityCount = BuildingPoc::where('owner_association_id',auth()->user()->owner_association_id)->where('building_id',$buildingId)->where('active',true)->count();
        }

        $vendorsQuery = Vendor::where('owner_association_id', Filament::getTenant()->id);

        // Apply building filter if selected
        if ($buildingId) {
            $vendorsQuery->whereHas('buildings', function ($q) use ($buildingId) {
                $q->where('building_id', $buildingId);
            });
        }

        // Apply date filters to vendor query
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $vendorsQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $vendorsQuery->where('created_at', '<=', $endOfDay);
        }

        $vendorsCount = $vendorsQuery->count();

        $technicianCount = User::where('role_id', $role->where('name', 'Technician')->value('id'))->count();

        // Pending User Approvals Query
        $userApprovalQuery = UserApproval::where('owner_association_id', Filament::getTenant()->id)
        ->where(function ($query) {
            $query->whereNull('status')
                  ->orWhereNotIn('status', ['approved', 'rejected']);
        });

        // Apply date filters to user approval query
        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $userApprovalQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $userApprovalQuery->where('created_at', '<=', $endOfDay);
        }

        // Apply building filter to user approval query if selected
        if ($buildingId) {
            $userApprovalQuery->whereHas('flat.building', function ($q) use ($buildingId) {
                $q->where('id', $buildingId);
            });
        }

        $pendingUserApprovalCount = $userApprovalQuery->count();

        $user = User::find(auth()->user()->id);
        $stats = [];

        // Check if the user has the necessary role for each stat and conditionally add the stat
        if ($user->can('view_any_vendor::vendor')) {
            $stats[] = Stat::make('Total Vendors', $vendorsCount)
                ->description('Vendors')
                ->icon('heroicon-o-briefcase')
                ->color('emerald-200')
                ->chart([10, 20, 30, 40, 50])
                ->extraAttributes(['style' => 'background-color: #E6F4EA; color: #006400;']);
        }

        if (Role::where('id', auth()->user()->role_id)->whereIn('name', ['OA', 'MD'])->exists()) {
            $stats[] = Stat::make('Total Technicians', $technicianCount)
                ->description('Technicians')
                ->icon('heroicon-o-wrench')
                ->color('blue')
                ->chart([12, 22, 32, 42, 52])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E0F2FF, #90CDF4); color: #1D4ED8;']);
        }

        if (Role::where('id', auth()->user()->role_id)->whereIn('name', ['OA', 'MD'])->exists()) {
            $stats[] = Stat::make('Total Gatekeepers', $securityCount)
                ->description('Gatekeepers')
                ->icon('heroicon-s-shield-check')
                ->color('red-200')
                ->chart([3, 13, 23, 33, 43])
                ->extraAttributes(['style' => 'background-color: #FDE2E2; color: #FF4500;']);
        }

        if ($user->can('view_any_user::approval')) {
            $stats[] = Stat::make('Resident Approvals', $pendingUserApprovalCount)
                ->description('Pending Resident Approvals')
                ->icon('heroicon-o-user')
                ->color('orange-200')
                ->chart([5, 15, 25, 35, 45])
                ->extraAttributes(['style' => 'background-color: #FFF7E0; color: #FFAA00;']);
        }

        return $stats;
    }
}
