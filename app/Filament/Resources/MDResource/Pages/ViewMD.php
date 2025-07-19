<?php

namespace App\Filament\Resources\MDResource\Pages;

use App\Filament\Resources\MDResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMD extends ViewRecord
{
    protected static string $resource = MDResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
