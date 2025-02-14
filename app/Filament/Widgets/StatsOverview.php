<?php

namespace App\Filament\Widgets;

use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\FlatOwners;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\User\User;
use App\Models\UserApproval;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    // protected int | string | array $columnSpan = 2;
    protected function getColumns(): int
{
    return 4; // Set the number of columns for the layout
}

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $buildingId = $this->filters['building'] ?? null;

        // Building Count
        $buildings = Building::where('owner_association_id', Filament::getTenant()->id)->count();

        // Owners and Tenants Queries
        $ownerQuery = FlatTenant::where('role', 'Owner')
            ->where('owner_association_id', Filament::getTenant()->id);
        $tenantQuery = FlatTenant::where('role', 'Tenant')
            ->where('owner_association_id', Filament::getTenant()->id);

        // WDA Query
        $wdaQuery = WDA::query()
            ->where('owner_association_id', Filament::getTenant()->id)
            ->where('status', 'pending');

        // Security, Vendors, Technicians, User Approvals Queries
        $securityQuery = BuildingPoc::where('owner_association_id', auth()->user()->owner_association_id)->where('active', true);
        $vendorsQuery = Vendor::where('owner_association_id', Filament::getTenant()->id);
        $userApprovalQuery = UserApproval::where('owner_association_id', Filament::getTenant()->id)
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['approved', 'rejected']);
            });

        // Apply filters
        if ($buildingId) {
            $ownerQuery->where('building_id', $buildingId);
            $tenantQuery->where('building_id', $buildingId);
            $wdaQuery->where('building_id', $buildingId);
            $securityQuery->where('building_id', $buildingId);
            $vendorsQuery->whereHas('buildings', function ($q) use ($buildingId) {
                $q->where('building_id', $buildingId);
            });
            $userApprovalQuery->whereHas('flat.building', function ($q) use ($buildingId) {
                $q->where('id', $buildingId);
            });
        }

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $ownerQuery->where('created_at', '>=', $startOfDay);
            $tenantQuery->where('created_at', '>=', $startOfDay);
            $wdaQuery->where('created_at', '>=', $startOfDay);
            $vendorsQuery->where('created_at', '>=', $startOfDay);
            $userApprovalQuery->where('created_at', '>=', $startOfDay);
        }

        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $ownerQuery->where('created_at', '<=', $endOfDay);
            $tenantQuery->where('created_at', '<=', $endOfDay);
            $wdaQuery->where('created_at', '<=', $endOfDay);
            $vendorsQuery->where('created_at', '<=', $endOfDay);
            $userApprovalQuery->where('created_at', '<=', $endOfDay);
        }

        // Get the counts
        $ownerCount = $ownerQuery->select('tenant_id')->distinct()->count('tenant_id');
        $tenantCount = $tenantQuery->select('tenant_id')->distinct()->count('tenant_id');
        $wdaCount = $wdaQuery->count();
        $securityCount = $securityQuery->count();
        $vendorsCount = $vendorsQuery->count();
        $pendingUserApprovalCount = $userApprovalQuery->count();

        $role = Role::where('owner_association_id', Filament::getTenant()->id);
        $technicianCount = User::where('role_id', $role->where('name', 'Technician')->value('id'))->count();

        $user = User::find(auth()->user()->id);
        $stats = [];

        // Add stats conditionally based on permissions
        if ($user->can('view_any_building::building')) {
            $stats[] = Stat::make('Total Buildings', $buildings)
                ->description('Buildings')
                ->icon('heroicon-s-building-office-2')
                ->color('blue')
                ->chart([12, 22, 32, 42, 52])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E0F2FF, #90CDF4); color: #1D4ED8;']);
        }

        if ($user->can('view_any_user::owner')) {
            $stats[] = Stat::make('Total Owners', $ownerCount)
                ->description('Owners')
                ->icon('heroicon-o-user-group')
                ->color('green')
                ->chart([10, 30, 50, 70, 90])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E6F4EA, #A7F3D0); color: #10B981;']);
        }

        if ($user->can('view_any_user::tenant')) {
            $stats[] = Stat::make('Total Tenants', $tenantCount)
                ->description('Tenants')
                ->icon('heroicon-o-users')
                ->color('orange')
                ->chart([15, 25, 35, 45, 55])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #FFF7E0, #FED7AA); color: #F97316;']);
        }

        if ($user->can('view_any_w::d::a')) {
            $stats[] = Stat::make('WDA', $wdaCount)
                ->description('Pending WDA')
                ->icon('heroicon-o-chart-bar-square')
                ->color('purple')
                ->chart([15, 25, 35, 45, 55])
                ->extraAttributes(['style' => 'background: linear-gradient(135deg, #EDE9FE, #C4B5FD); color: #8B5CF6;']);
        }

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
