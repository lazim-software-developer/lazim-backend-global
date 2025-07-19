<?php
namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Models\Master\Role;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title   = 'Residents';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        $userRoleName = Role::where('id', $user->role_id)->value('name');

        // $approvedTenants = FlatTenant::where('active', true)->pluck('tenant_id')->toArray();

        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if (auth()->user()?->role?->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('active', true)
                ->whereIn('flat_id', $pmFlats)
                ->whereNotIn('tenant_id', function ($query) {
                    $query->select('user_id')
                        ->from('user_approvals')
                        ->whereNotIn('status', ['approved'])
                        ->where('status', null);
                });
        } elseif ($userRoleName == 'OA') {
            return parent::getTableQuery()->whereIn('building_id', $pmbuildingIds);

        }
        return parent::getTableQuery();
    }
}
