<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReserveFundStatementMollak extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reserve-fund-statement-mollak';

    protected static ?string $title = 'Reserve Fund Statement Mollak';

    protected static ?string $slug = 'mollak-reserve-fund-statement';

    public function getViewData(): array
    {
        // $currentYear = Carbon::now()->year;
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            $buildings = Building::all();
        }
        else{
            $buildings_id = DB::table('building_owner_association')->where('owner_association_id',Filament::getTenant()->id)->where('active', true)->pluck('building_id');
            $buildings = Building::whereIn('id', $buildings_id)->get();
        }
        return [
            // 'years' => range($currentYear, Carbon::now()->subYears(5)->year),
            'buildings' =>  $buildings,
            "message" => "Please Select a building and Year",
        ];
    }
}
