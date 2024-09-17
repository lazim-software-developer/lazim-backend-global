<?php

namespace App\Filament\Resources\WDAResource\Pages;

use Filament\Actions;
use App\Filament\Resources\WDAResource;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWDAS extends ListRecords
{
    protected static ?string $title = 'WDA';
    protected static string $resource = WDAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('vendor_id',Vendor::whereHas('ownerAssociation', function ($query) {
            $query->where('owner_association_id', Filament::getTenant()->id);
        })->pluck('id'));
    }
}
