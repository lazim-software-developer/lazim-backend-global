<?php
namespace App\Filament\Resources\PatrollingResource\Pages;

use App\Filament\Resources\PatrollingResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPatrollings extends ListRecords
{
    protected static string $resource = PatrollingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $role            = auth()->user()->role->name;
        $buildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        if ($role == 'Admin') {
            return parent::getTableQuery()->latest();
        }
        if (in_array($role, ['Property Manager', 'OA'])) {
            return parent::getTableQuery()
                ->whereIn('building_id', $buildings)
                ->latest();
        }
        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()->id)->latest();
    }
}
