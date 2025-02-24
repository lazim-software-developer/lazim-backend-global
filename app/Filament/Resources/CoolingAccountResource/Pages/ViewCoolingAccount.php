<?php

namespace App\Filament\Resources\CoolingAccountResource\Pages;

use App\Filament\Resources\CoolingAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCoolingAccount extends ViewRecord
{
    protected static string $resource = CoolingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
