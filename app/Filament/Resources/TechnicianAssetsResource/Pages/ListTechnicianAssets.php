<?php

namespace App\Filament\Resources\TechnicianAssetsResource\Pages;

use App\Filament\Resources\TechnicianAssetsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianAssets extends ListRecords
{
    protected static string $resource = TechnicianAssetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
