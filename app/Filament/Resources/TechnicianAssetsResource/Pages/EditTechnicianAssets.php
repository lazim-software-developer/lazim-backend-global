<?php

namespace App\Filament\Resources\TechnicianAssetsResource\Pages;

use App\Filament\Resources\TechnicianAssetsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTechnicianAssets extends EditRecord
{
    protected static string $resource = TechnicianAssetsResource::class;
    protected static ?string $title = 'Technician asset';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
