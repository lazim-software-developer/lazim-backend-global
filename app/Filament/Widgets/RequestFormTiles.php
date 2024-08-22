<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\WDA;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\NocForms;
use App\Models\Forms\SaleNOC;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RequestFormTiles extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
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

        return [
            $this->getTotalPendingMoveInCount(),
            $this->getTotalPendingFitoutRequest(),
            $this->getPendingNocCount()
        ];
    }

    private function getTotalPendingMoveInCount()
    {
        // Contracts
        $query = MoveInOut::where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', '');
        });

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        // Return the Stat widget
        return Stat::make('Pending Move-In Requests', $query->count())
            ->descriptionIcon('heroicon-s-flag')
            ->chart(array_values($chartData))
            ->color('info'); // Color to represent open complaints
    }

    private function getTotalPendingFitoutRequest()
    {
        // Base query for WDA
        $query = FitOutForm::where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', '');
        });

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        return Stat::make('Pending Fitout Requests', $query->count())
            ->descriptionIcon('heroicon-s-user-group')
            ->chart(array_values($chartData))
            ->color('warning');
    }

    private function getPendingNocCount()
    {
        // Base query for WDA
        $query = SaleNOC::where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', '');
        });

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        return Stat::make('Pending Sale NOC', $query->count())
            ->descriptionIcon('heroicon-s-user-group')
            ->chart(array_values($chartData))
            ->color('warning');
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
            $query->whereIn('building_id', Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'));
        }
    }
}
