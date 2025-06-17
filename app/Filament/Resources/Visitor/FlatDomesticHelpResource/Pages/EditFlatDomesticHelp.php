<?php

namespace App\Filament\Resources\Visitor\FlatDomesticHelpResource\Pages;

use App\Filament\Resources\Visitor\FlatDomesticHelpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlatDomesticHelp extends EditRecord
{
    protected static string $resource = FlatDomesticHelpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
