<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Filament\Resources\UserApprovalResource;
use App\Models\Master\Role;
use DB;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUserApprovals extends ListRecords
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $flats = DB::table('flats')->whereIn('building_id', $pmbuildingIds)->pluck('id')->toArray();
        if (auth()->user()->role->name == 'Property Manager') {
            return parent::getTableQuery()
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->whereIn('flat_id', $flats)
                ->latest();
        }
        $tenant = Filament::getTenant();
        if ($tenant) {
            return parent::getTableQuery()->where('owner_association_id', $tenant->id)->latest();
        }
        return parent::getTableQuery()->latest();
    }
}
