<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Imports\BillImport;
use App\Models\Building\Building;
use Auth;
use Carbon\Carbon;
use DB;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()->label('New Bill'),

            Action::make('upload')
                ->slideOver()
                ->color("primary")
                ->form([
                    Grid::make(2)
                        ->schema([
                            Select::make('building_id')
                                ->required()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn(Set $set) => $set('flat_id', null))
                                ->options(function () {
                                    if (auth()->user()->role->name == 'Admin') {
                                        return Building::pluck('name', 'id');
                                    } elseif (auth()->user()->role->name == 'Property Manager') {
                                        $buildingIds = DB::table('building_owner_association')
                                            ->where('owner_association_id', auth()->user()->owner_association_id)
                                            ->where('active', true)
                                            ->pluck('building_id');

                                        return Building::whereIn('id', $buildingIds)
                                            ->pluck('name', 'id');
                                    } else {
                                        $oaId = auth()->user()?->owner_association_id;
                                        return Building::where('owner_association_id', $oaId)
                                            ->pluck('name', 'id');
                                    }
                                })
                                ->searchable()
                                ->label('Building Name'),

                            DatePicker::make('month')
                                ->required()
                                ->default(now())
                                ->native(false)
                                ->displayFormat('m-Y')
                                ->helperText('Enter the month for which this bill is generated'),

                            Select::make('type')
                                ->required()
                                ->options([
                                    'BTU'               => 'BTU',
                                    'DEWA'              => 'DEWA',
                                    'lpg'               => 'LPG',
                                    'Telecommunication' => 'DU/Etisalat',
                                ])
                                ->label('Bill Type'),

                            FileUpload::make('excel_file')
                                ->label('Bills Excel Data')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                ])
                                ->required(),
                        ]),

                ])
                ->action(function (array $data) {
                    $month    = Carbon::parse($data['month'])->format('Y-m-d');
                    $filePath = storage_path('app/public/' . $data['excel_file']);

                    // Import bills
                    Excel::import(new BillImport(
                        $data['building_id'],
                        $month,
                        $data['type']
                    ), $filePath);
                }),

            ExportAction::make()->exports([
                ExcelExport::make()
                    ->withColumns([
                        Column::make('unit_number')
                            ->heading('Unit Number'),
                        Column::make('bill_number')
                            ->heading('Bill Number'),
                        Column::make('amount')
                            ->heading('Amount'),
                        Column::make('due_date')
                            ->heading('Due Date'),
                        Column::make('status')
                            ->heading('Status'),
                    ])
                    ->modifyQueryUsing(function ($query) {
                        return DB::table(DB::raw('(SELECT 1) as dummy'))
                            ->select([
                                DB::raw("'' as unit_number"),
                                DB::raw("'' as bill_number"),
                                DB::raw("'' as amount"),
                                DB::raw("'' as due_date"),
                                DB::raw("'' as status"),
                            ])
                            ->orderBy('unit_number');
                    }),
            ])->label('Download sample file'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'BTU'         => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'BTU')),
            'DEWA'        => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'DEWA')),
            'DU/Etisalat' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'Telecommunication')),
            'lpg'         => Tab::make()
                ->label('LPG')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'lpg')),
        ];
    }
}
