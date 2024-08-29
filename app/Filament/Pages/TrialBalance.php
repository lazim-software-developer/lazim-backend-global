<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TrialBalance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.trial-balance';

    protected static ?string $title = 'Trial Balance';

    protected static ?string $slug = 'trial-balance';

    public function getViewData(): array
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            $buildings = Building::all();
        }
        else{
            $buildings_id = DB::table('building_owner_association')->where('owner_association_id',Filament::getTenant()->id)->where('active', true)->pluck('building_id');
            $buildings = Building::whereIn('id', $buildings_id)->get();
        }
        return [
            'buildings' => $buildings,
            "message" => "Please Select a building and date",
        ];
    }
}
