<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\WDA;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Building\Building;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class VendorDashTiles extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
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
            $this->getTotalContract(),
            $this->getPendingWDAStat(),
            $this->getPendingInvoiceStat()
        ];
    }

    private function getTotalContract()
    {
        // Contracts
        $query = Contract::query();

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        // Return the Stat widget
        return Stat::make('Total Contracts', $query->count())
            ->icon('heroicon-s-document-text')
            ->chart(array_values($chartData))
            ->color('danger'); // Color to represent open complaints
    }

    private function getPendingWDAStat()
    {
        // Base query for WDA
        $query = WDA::where('status', 'pending');

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        return Stat::make('Pending WDA', $query->count())
            ->icon('heroicon-s-check-circle')
            ->chart(array_values($chartData))
            ->color('warning');
    }

    private function getPendingInvoiceStat()
    {
        // Base query for WDA
        $query = Invoice::where('status', 'pending');

        $this->applyBuildingAndRoleFilters($query);

        // Prepare chart data for the last 4 months
        $chartData = $this->getMonthlyChartData(clone $query);

        return Stat::make('Pending Invoices', $query->count())
            ->icon('heroicon-s-receipt-tax')
            ->chart(array_values($chartData))
            ->color('danger');
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
