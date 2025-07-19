<?php

namespace App\Filament\Resources\ResidentialFormResource\Pages;

use App\Filament\Resources\ResidentialFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResidentialForm extends ViewRecord
{
    protected static string $resource = ResidentialFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
