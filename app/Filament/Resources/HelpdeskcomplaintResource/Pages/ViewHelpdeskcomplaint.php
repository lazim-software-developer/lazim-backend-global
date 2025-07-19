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
}
