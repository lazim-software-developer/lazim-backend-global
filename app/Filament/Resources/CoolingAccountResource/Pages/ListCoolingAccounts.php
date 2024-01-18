<?php

namespace App\Filament\Resources\CoolingAccountResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Building\Building;
use Filament\Actions\SelectAction;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CoolingAccountImport;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Filament\Resources\CoolingAccountResource;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

class ListCoolingAccounts extends ListRecords
{
    protected static string $resource = CoolingAccountResource::class;
    protected static ?string $title = 'Cooling account';

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

                ExportAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        Column::make('flat_id')->heading('Unit No'),
                        Column::make('opening_balance')->heading('Opening balance : receivable/ (advance)'),
                        Column::make('consumption')->heading('In-unit consumption'),
                        Column::make('demand_charge')->heading('In-unit demand charge'),
                        Column::make('security_deposit')->heading('In-unit security deposit'),
                        Column::make('billing_charges')->heading('In-unit billing charges'),
                        Column::make('other_charges')->heading('In-unit other charges'),
                        Column::make('receipts')->heading('Receipts'),
                        Column::make('closing_balance')->heading('Closing balance'),
                    ])
                    ->modifyQueryUsing(fn ($query) => $query->where('id', 0)),
                ])
        ];
    }
}
