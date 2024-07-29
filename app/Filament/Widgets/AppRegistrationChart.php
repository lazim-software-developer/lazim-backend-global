<?php

namespace App\Filament\Widgets;

use App\Models\Building\FlatTenant;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AppRegistrationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'App Registrations';
    protected static ?string $maxHeight = '200px';
    // protected static ?string $maxWidt = '200px';
    protected static ?int $sort = 12;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = FlatTenant::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $query->where('created_at', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            $query->where('created_at', '<=', $endOfDay);
        }

        $owners = clone $query;
        $tenants = clone $query;

        $ownersCount = $owners->where('role','Owner')->count();
        $TenantsCount = $tenants->where('role','Tenant')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Owners',
                    'data' => [$ownersCount],
                    'backgroundColor' => '#f5fa5a',
                    'borderColor' => '#ffffff',
                ],
                [
                    'label' => 'Tenants',
                    'data' => [$TenantsCount],
                    'backgroundColor' => '#bbf76d',
                    'borderColor' => '#ffffff',
                ], 
                ],
                'labels' => [''],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
