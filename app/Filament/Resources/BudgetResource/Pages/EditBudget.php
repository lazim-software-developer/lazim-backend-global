<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Filament\Actions;
use Filament\Tables\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BudgetResource;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Action::make('create tender')
            //         ->label('Create Tender')
            //         ->url(route('tender.create', ['budget' => $this]))
        ];
    }
}
