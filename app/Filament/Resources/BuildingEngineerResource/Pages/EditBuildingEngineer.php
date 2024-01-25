<?php

namespace App\Filament\Resources\BuildingEngineerResource\Pages;

use App\Filament\Resources\BuildingEngineerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuildingEngineer extends EditRecord
{
    protected static string $resource = BuildingEngineerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
