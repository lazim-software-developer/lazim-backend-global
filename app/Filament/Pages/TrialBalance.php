<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use Filament\Pages\Page;

class TrialBalance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.trial-balance';

    protected static ?string $title = 'Trial Balance';

    protected static ?string $slug = 'trial-balance';

    public function getViewData(): array
    {
        return [
            'buildings' => Building::where('owner_association_id', auth()->user()->owner_association_id)->get(),
            "message" => "Please Select a building and Year",
        ];
    }
}
