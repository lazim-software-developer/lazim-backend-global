<?php

namespace App\Filament\Resources\Building\BuildingPocResource\Pages;

use App\Filament\Resources\Building\BuildingPocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuildingPoc extends EditRecord
{
    protected static string $resource = BuildingPocResource::class;
    protected ?string $heading        = 'Building Manager';
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
