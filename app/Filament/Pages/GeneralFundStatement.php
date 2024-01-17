<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use Carbon\Carbon;
use Filament\Pages\Page;

class GeneralFundStatement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.general-fund-statement';

    protected static ?string $title = 'General Fund Statement';

    protected static ?string $slug = 'general-fund-statement';

    public function getViewData(): array
    {
        $currentYear = Carbon::now()->year;
        return [
            'years' => range($currentYear, Carbon::now()->subYears(5)->year),
            'buildings' => Building::where('owner_association_id', auth()->user()->owner_association_id)->get(),
            "message" => "Please Select a building and Year",
        ];
    }
    
}
