<?php

namespace App\Filament\Widgets;

use App\Models\User\User;
use App\Models\Master\Role;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class RegistrationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Registration';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

            $query = User::query();
    
            if ($startDate) {
                $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $query->where('created_at', '>=', $startOfDay);
            }
            if ($endDate) {
                $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                $query->where('created_at', '<=', $endOfDay);
            }

            $residentsRole = Role::where('owner_association_id',Filament::getTenant()->id)->WhereIn('name',['Owner','Tenant'])->pluck('id');
            $vendorsRole = Role::where('owner_association_id',Filament::getTenant()->id)->Where('name','Vendor')->pluck('id');
            // Log::info($residentsRole);

            $vendorUser = clone $query; 
            $residentUser = clone $query;


            // $vendors = User::where('role_id', 2)->where('owner_association_id', auth()->user()->owner_association_id)->count();
            // $residents = User::whereIn('role_id', [1, 11])->where('owner_association_id', auth()->user()->owner_association_id)->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Vendors', 'Residents'],
                        'data' => [ $vendorUser->whereIn('role_id',$vendorsRole)->count(), $residentUser->whereIn('role_id',$residentsRole)->count()],
                        'backgroundColor' => ['#fa5a5a', '#fa9a5a'],
                        'borderColor' => '#ffffff',
                    ],  
                ],
                'labels' => ['Vendors', 'Residents'],
            ];
        
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
