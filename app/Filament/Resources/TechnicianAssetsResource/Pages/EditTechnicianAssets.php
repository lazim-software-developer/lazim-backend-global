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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
