<?php
 
namespace App\Filament\Widgets;

use App\Models\Building\Flat;
use App\Models\FlatOwners;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class OwnerOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
 
    protected function getStats(): array
    {
        //contracts count 
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Contract::query()->where('owner_association_id', Filament::getTenant()->id);

        if ($startDate) {
            $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $query->where('start_date', '>=', $startOfDay);
        }
        if ($endDate) {
            $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
            $query->where('end_date', '<=', $endOfDay);
        }
        

        $contractCount = $query->count();
        
        //Flat owneres and tenants
        $flats = Flat::where('owner_association_id', Filament::getTenant()->id)->pluck('id');
        $owners = FlatOwners::whereIn('flat_id',$flats)->distinct('flat_id')->count();

        $tenants = MollakTenant::where('owner_association_id', Filament::getTenant()->id)->distinct('email')->count();

        return [
            Stat::make('Owners',$owners)
                // ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('info'),
 
            Stat::make('Tenants',$tenants)
                // ->chart([60, 92, 33, 80, 31, 98, 70])
                ->Color('success'),

            Stat::make('Contracts', $contractCount)
                ->descriptionIcon('heroicon-s-user-group')
                // ->chart([60, 92, 33, 80, 31, 98, 70])
                ->color('info'),
        ];
    }
}