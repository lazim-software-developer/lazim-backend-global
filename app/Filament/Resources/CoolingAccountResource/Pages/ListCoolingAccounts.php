<?php

namespace App\Filament\Resources\CoolingAccountResource\Pages;

use App\Filament\Resources\CoolingAccountResource;
use App\Imports\CoolingAccountImport;
use App\Models\Building\Building;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\SelectAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ListCoolingAccounts extends ListRecords
{
    protected static string $resource = CoolingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
                
                Action::make('upload')
                    ->slideOver()
                    ->color("primary")
                    ->form([
                        Select::make('building_id')
                        ->required()
                        ->relationship('building', 'name')
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            // dd($tenants);
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->label('Building Name'),
                        FileUpload::make('excel_file')
                        ->label('Cooling Accounts Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required(),
                        Select::make('month')
                        ->searchable()
                        ->required()
                        ->placeholder('Select Month')
                        ->options([
                            'january' => 'January',
                            'february' => 'February',
                            'march' => 'March',
                            'april' => 'April',
                            'may' => 'May',
                            'june' => 'June',
                            'july' => 'July',
                            'august' => 'August',
                            'september' => 'September',
                            'october' => 'October',
                            'november' => 'November',
                            'december' => 'December',
                        ]),
                        Select::make('year')
                        ->required()
                        ->searchable()
                        ->placeholder('Select Year')
                        ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    $month = $data['month'].$data['year'];
                    $filePath = $data['excel_file']; // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    Log::info("Full path: ", [$fullPath]);
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // Now import using the file path
                    Excel::import(new CoolingAccountImport( $buildingId, $month ), $fullPath);
                    
                }),
        ];
    }
}
