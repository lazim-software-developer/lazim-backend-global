<?php

namespace App\Filament\Resources\Master\FacilityResource\Pages;

use App\Filament\Resources\Master\FacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacility extends EditRecord
{
    protected static string $resource = FacilityResource::class;

    public function getTitle(): string
    {
        return 'Edit Amenity';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
