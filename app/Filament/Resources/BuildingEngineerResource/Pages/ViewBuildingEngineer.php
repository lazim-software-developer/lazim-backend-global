<?php

namespace App\Filament\Resources\BuildingEngineerResource\Pages;

use App\Filament\Resources\BuildingEngineerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBuildingEngineer extends ViewRecord
{
    protected static string $resource = BuildingEngineerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
