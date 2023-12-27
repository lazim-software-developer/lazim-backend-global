<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Filament\Actions;
use App\Imports\MyBudgetImport;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource;
use EightyNine\ExcelImport\ExcelImportAction;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;
    protected static ?string $title = 'Budgets';
    protected function getTableQuery(): Builder
    {
        $buildings = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        return parent::getTableQuery()->whereIn('building_id',$buildings);
    }

    protected function getHeaderActions(): array
    {
        return [
            // ExcelImportAction::make()
            //     ->slideOver()
            //     ->color("primary")
            //     ->use(MyBudgetImport::class),
        ];
    }
}
