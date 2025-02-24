<?php

namespace App\Filament\Resources\CoolingAccountResource\Pages;

use App\Filament\Resources\CoolingAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoolingAccount extends EditRecord
{
    protected static string $resource = CoolingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
