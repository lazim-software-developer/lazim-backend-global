<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Filament\Resources\ComplaintscomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaintscomplaints extends ViewRecord
{
    protected static string $resource = ComplaintscomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
