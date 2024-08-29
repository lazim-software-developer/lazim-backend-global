<?php

namespace App\Filament\Resources\Accounting\BudgetResource\Pages;

use App\Filament\Resources\Accounting\BudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBudget extends CreateRecord
{
    protected static string $resource = BudgetResource::class;
}
