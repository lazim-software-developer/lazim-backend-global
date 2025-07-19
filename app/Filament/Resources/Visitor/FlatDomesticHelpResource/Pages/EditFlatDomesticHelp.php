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
            Actions\DeleteAction::make(),
        ];
    }
}
