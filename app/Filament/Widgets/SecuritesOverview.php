<?php
 
namespace App\Filament\Widgets;
 
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
 
class SecuritesOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    // protected int | string | array $columnSpan = 4;

    
 
    protected function getStats(): array
    {
        $role = Role::where('owner_association_id',Filament::getTenant()->id);
    
        $securityCount = User::where('role_id', $role->where('name','Security')->value('id'))->count();
 
        $vendorsCount = Vendor::where('owner_association_id',Filament::getTenant()->id)->count();

        $technicianCount = User::where('role_id',$role->where('name','Technician')->value('id'))->count();
 
        return [
            Stat::make('Total Vendors', $vendorsCount)
                ->description('Vendors associated with the building')
                ->icon('heroicon-o-briefcase')
                ->color('emerald-200') // Softer shade of green
                ->chart([10, 20, 30, 40, 50]) // Example dynamic chart data
                ->extraAttributes(['style' => 'background-color: #E6F4EA; color: #006400;']),
            
            Stat::make('Total Technicians', $technicianCount)
                ->description('Active Technicians in the system')
                ->icon('heroicon-o-wrench')
                ->color('blue-200') // Softer shade of blue
                ->chart([5, 15, 25, 35, 45]) // Example dynamic chart data
                ->extraAttributes(['style' => 'background-color: #E3F2FD; color: #1E90FF;']),
            
            Stat::make('Total Securities', $securityCount)
                ->description('Security personnel count')
                ->icon('heroicon-s-shield-check')
                ->color('red-200') // Softer shade of red
                ->chart([3, 13, 23, 33, 43]) // Example dynamic chart data
                ->extraAttributes(['style' => 'background-color: #FDE2E2; color: #FF4500;']),
        
        ];
    }
}