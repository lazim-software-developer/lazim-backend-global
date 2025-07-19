<?php

namespace App\Filament\Resources\ComplaintOfficerResource\Pages;

use App\Filament\Resources\ComplaintOfficerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaintOfficer extends ViewRecord
{
    protected static string $resource = ComplaintOfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
