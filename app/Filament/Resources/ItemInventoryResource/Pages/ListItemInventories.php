<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Filament\Resources\ItemInventoryResource;
use App\Models\Item;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListItemInventories extends ListRecords
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventory';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $itemId = Item::whereIn('building_id', $buildingIds)
            ->pluck( 'id');


        if (auth()->user()?->role?->name === 'Property Manager') {
            return parent::getTableQuery()->whereIn('item_id', $itemId);
        }

        elseif(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()?->id);
    }
}
