<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetMaintenance extends CreateRecord
{
    protected static string $resource = AssetMaintenanceResource::class;
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
