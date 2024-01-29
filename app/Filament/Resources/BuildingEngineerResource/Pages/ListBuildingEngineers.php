<?php

namespace App\Filament\Resources\BuildingEngineerResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BuildingEngineerResource;

class ListBuildingEngineers extends ListRecords
{
    protected static string $resource = BuildingEngineerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(['owner_association_id' => auth()->user()->owner_association_id, 'role_id' => Role::where('name', 'Building Engineer')->first()->id]);
    }
}
