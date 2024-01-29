<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use Filament\Pages\Page;

class ReserveFundStatementMollak extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reserve-fund-statement-mollak';

    protected static ?string $title = 'Reserve Fund Statement Mollak';

    protected static ?string $slug = 'reserve-fund-statement-mollak';

    public function getViewData(): array
    {
        // $currentYear = Carbon::now()->year;
        return [
            // 'years' => range($currentYear, Carbon::now()->subYears(5)->year),
            'buildings' => Building::where('owner_association_id', auth()->user()->owner_association_id)->get(),
            "message" => "Please Select a building and Year",
        ];
    }
}
