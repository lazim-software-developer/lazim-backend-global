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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
