<?php

namespace App\Filament\Pages;

use App\Imports\ReserveFundStatementImport;
use App\Models\Building\Building;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
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
        return [
            'years' => range($currentYear, Carbon::now()->subYears(5)->year),
            'buildings' => Building::where('owner_association_id', auth()->user()->owner_association_id)->get(),
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
                        Select::make('building_id')
                        ->required()
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            // dd($tenants);
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->label('Building Name'),
                        FileUpload::make('excel_file')
                        ->label('Reserve Fund Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required(),
                        DatePicker::make('statement_date')->required(),
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    $date = $data['statement_date'];
                    $filePath = $data['excel_file'];
                    // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    // Log::info("Full path: ", [$fullPath]);
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
