<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnagging extends EditRecord
{
    protected static string $resource = SnaggingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
