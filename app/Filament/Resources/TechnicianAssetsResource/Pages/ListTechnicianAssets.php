<?php

namespace App\Filament\Resources\TechnicianAssetsResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TechnicianAssetsResource;
use App\Models\Master\Role;
use Filament\Facades\Filament;

class ListTechnicianAssets extends ListRecords
{
    protected static string $resource = TechnicianAssetsResource::class;
    protected static ?string $title = 'Technician assets';
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('owner_association_id',Filament::getTenant()?->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
