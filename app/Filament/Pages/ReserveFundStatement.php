<?php

namespace App\Filament\Pages;

use App\Imports\ReserveFundStatementImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReserveFundStatement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reserve-fund-statement';
    
    protected static ?string $title = 'Reserve Fund Statement';

    protected static ?string $slug = 'reserve-fund-statement';

    public function getViewData(): array
    {
        $currentYear = Carbon::now()->year;
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            $buildings = Building::all();
        }
        else{
            $buildings_id = DB::table('building_owner_association')->where('owner_association_id',Filament::getTenant()->id)->where('active', true)->pluck('building_id');
            $buildings = Building::whereIn('id', $buildings_id)->get();
        }
        return [
            'years' => range($currentYear, Carbon::now()->subYears(5)->year),
            'buildings' => $buildings,
            "message" => "Please Select a building and Year",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

                Action::make('upload')
                    ->slideOver()
                    ->color("primary")
                    ->form([
                        Grid::make(2)
                        ->schema([
                            Select::make('building_id')
                            ->required()
                            ->options(function () {
                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return Building::all()->pluck('name', 'id');
                                }
                                else{
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->pluck('name', 'id');
                                } 
                            })
                            ->searchable()
                            ->label('Building Name'),
                            DatePicker::make('statement_date')->required(),
                            FileUpload::make('excel_file')
                            ->label('Reserve Fund Excel Data')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                                'application/vnd.ms-excel', // for .xls
                            ])
                            ->required(),
                        ])
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    $date = $data['statement_date'];
                    $filePath = $data['excel_file'];
                    // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // // Now import using the file path
                    Excel::import(new ReserveFundStatementImport( $buildingId, $date ), $fullPath);

                }),
        ];
    }
}
