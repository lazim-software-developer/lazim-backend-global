<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['show_inhouse_services'] = 0;
        $data['managed_by']        = 'Property Manager';
        return $data;
    }
}
