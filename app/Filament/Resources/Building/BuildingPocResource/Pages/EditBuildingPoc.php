<?php

namespace App\Filament\Resources\Building\BuildingPocResource\Pages;

use App\Filament\Resources\Building\BuildingPocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuildingPoc extends EditRecord
{
    protected static string $resource = BuildingPocResource::class;
    protected ?string $heading        = 'Security';
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), # TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
