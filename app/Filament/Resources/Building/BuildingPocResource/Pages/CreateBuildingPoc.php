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

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    protected function afterCreate()
    {
        BuildingPoc::where('id', $this->record->id)
            ->update([
                'active'=>1,
            ]);

    }
}
