<?php

namespace App\Filament\Resources\Building\BuildingPocResource\Pages;

use App\Filament\Resources\Building\BuildingPocResource;
use App\Models\Building\BuildingPoc;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateBuildingPoc extends CreateRecord
{
    protected ?string $heading        = 'Security';
    protected static string $resource = BuildingPocResource::class;

    protected function afterCreate()
    {
        BuildingPoc::where('id', $this->record->id)
            ->update([
                'active'=>1,
            ]);

    }
}
