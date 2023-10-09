<?php

namespace App\Filament\Resources\Master\FacilityResource\Pages;

use App\Filament\Resources\Master\FacilityResource;
use App\Models\Master\Facility;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFacility extends CreateRecord
{
    protected static string $resource = FacilityResource::class;

    protected function afterCreate(){

        Facility::where('id', $this->record->id)
            ->update([
                'active'=>1
            ]);

    }
}
