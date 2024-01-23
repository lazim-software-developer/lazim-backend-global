<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Imports\MyBudgetImport;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Filament\Resources\Building\BuildingResource;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin') 
        {
            return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
        }
        return parent::getTableQuery();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
