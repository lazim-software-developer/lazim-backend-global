<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Models\Building\Complaint;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewHelpdeskcomplaint extends ViewRecord
{
    protected static string $resource = HelpdeskcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
