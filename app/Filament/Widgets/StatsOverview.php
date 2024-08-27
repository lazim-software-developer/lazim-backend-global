<?php
 
namespace App\Filament\Widgets;
 
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use App\Models\MollakTenant;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
 
class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    // protected int | string | array $columnSpan = 4;

    
 
    protected function getStats(): array
    {
        $buildings = Building::where('owner_association_id',Filament::getTenant()->id)->count();

        $flats = Flat::where('owner_association_id', Filament::getTenant()->id)->pluck('id');
        $owners = FlatOwners::whereIn('flat_id',$flats)->distinct('flat_id')->count();

        $tenants = MollakTenant::where('owner_association_id', Filament::getTenant()->id)->distinct('email')->count();

        return [
            Stat::make('Total Buildings', $buildings)
            ->description('Buildings under management')
            ->icon('heroicon-s-building-office-2')
            ->color('blue')
            ->chart([12, 22, 32, 42, 52]) 
            ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E0F2FF, #90CDF4); color: #1D4ED8;']),
        
        Stat::make('Total Owners', $owners)
            ->description('Registered property owners')
            ->icon('heroicon-o-user-group')
            ->color('green')
            ->chart([10, 30, 50, 70, 90]) 
            ->extraAttributes(['style' => 'background: linear-gradient(135deg, #E6F4EA, #A7F3D0); color: #10B981;']),
        
        Stat::make('Total Tenants', $tenants)
            ->description('Tenants currently occupying flats')
            ->icon('heroicon-o-users')
            ->color('orange')
            ->chart([15, 25, 35, 45, 55]) 
            ->extraAttributes(['style' => 'background: linear-gradient(135deg, #FFF7E0, #FED7AA); color: #F97316;']),
  
 
        ];
    }
}