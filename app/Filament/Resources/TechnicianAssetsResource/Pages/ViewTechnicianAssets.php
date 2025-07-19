<?php

namespace App\Filament\Resources\TechnicianAssetsResource\Pages;

use App\Filament\Resources\TechnicianAssetsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTechnicianAssets extends ViewRecord
{
    protected static string $resource = TechnicianAssetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
