<?php

namespace App\Filament\Resources\CoolingAccountResource\Pages;

use App\Models\OwnerAssociation;
use DB;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Building\Building;
use Filament\Actions\SelectAction;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\Builder;
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
use App\Models\Master\Role;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\Grid;

class ListCoolingAccounts extends ListRecords
{
    protected static string $resource = CoolingAccountResource::class;
    protected static ?string $title = 'Cooling account';

    protected function getHeaderActions(): array
    {
        return [
                backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
                Action::make('upload')
                    ->slideOver()
                    ->modalWidth('md')
                    ->color("primary")
                    ->form([
                        Select::make('building_id')
                        ->required()
                        ->preload()
                        ->relationship('building', 'name')
                        ->options(function () {
                            if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                return Building::all()->pluck('name', 'id');
                            }
                            elseif(Role::where('id', auth()->user()->role->id)->first()->name == 'Property Manager'
                            || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                            ->pluck('role')[0] == 'Property Manager'){
                                $buildingIds = DB::table('building_owner_association')
                                    ->where('owner_association_id', auth()->user()->owner_association_id)
                                    ->where('active', true)
                                    ->pluck('building_id');

                                return Building::whereIn('id', $buildingIds)
                                    ->pluck('name', 'id');
                            }
                            else{
                                return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                            }
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

                        DateTimePicker::make('due_date')
                            ->withoutTime()
                            ->visible(function(){
                                if(auth()->user()->role->name == 'Property Manager'){
                                    return true;
                                }
                            }),
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    // dd($buildingId);
                    $month = $data['month'].$data['year'];

                    $dueDate = $data['due_date'] ?? null;
                    $status  = $data['status'] ?? null;

                    $filePath = $data['excel_file']; // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // Now import using the file path
                    Excel::import(new CoolingAccountImport( $buildingId, $month, $dueDate), $fullPath);

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
                        Column::make('status')->heading('Status'),
                    ])
                    ->modifyQueryUsing(fn ($query) => $query->where('id', 0)),
                ])->label('Download sample file')
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Get user role
        $userRole = auth()->user()?->role?->name;

        if ($userRole === 'Admin') {
            // Admin can see all records
            return $query;
        } elseif ($userRole === 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
            ->pluck('role')[0] == 'Property Manager') {
            // Get building IDs associated with the PM's owner association
            $buildingIds = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)  // Make sure to only get active associations
                ->pluck('building_id');

            return $query->whereIn('building_id', $buildingIds);
        } else {
            // For other roles, only show records from their owner association
            return $query->where('owner_association_id', auth()->user()?->owner_association_id);
        }
    }
}
