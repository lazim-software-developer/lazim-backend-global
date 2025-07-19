<?php

namespace App\Filament\Resources\ComplaintssuggessionResource\Pages;

use App\Filament\Resources\ComplaintssuggessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaintssuggession extends ViewRecord
{
    protected static string $resource = ComplaintssuggessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
