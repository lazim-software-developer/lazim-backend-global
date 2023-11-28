<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use App\Imports\MyBudgetImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\BudgetResource;
use EightyNine\ExcelImport\ExcelImportAction;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->slideOver()
                ->color("primary")
                ->use(MyBudgetImport::class),
        ];
    }
}
