<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpdeskcomplaint extends EditRecord
{
    protected static string $resource = HelpdeskcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
