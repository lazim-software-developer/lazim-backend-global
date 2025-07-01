<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BudgetResource;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
