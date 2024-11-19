<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Imports\BillImport;
use App\Models\Bill;
use App\Models\Building\Building;
use App\Models\Building\Flat;
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

                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->noSearchResultsMessage('No Flats found for this building.')
                                ->placeholder('Select the Flat')
                                ->options(function (callable $get) {
                                    return Flat::where('building_id', $get('building_id'))
                                        ->pluck('property_number', 'id');
                                })
                                ->disabled(function (callable $get) {
                                    if ($get('building_id') == null) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->helperText(function (callable $get) {
                                    if ($get('building_id') == null) {
                                        return 'Select the Building to load it\'s flats';
                                    }return '';
                                })
                                ->searchable()
                                ->required(),
                            FileUpload::make('excel_file')
                                ->label('Bills Excel Data')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                ])
                                ->required(),
                            DatePicker::make('month')
                                ->required()
                                ->default(now())
                                ->native(false)
                                ->displayFormat('m-Y')
                                ->helperText('Enter the month for which this bill is generated'),
                        ]),

                ])
                ->action(function (array $data) {
                    $month    = Carbon::parse($data['month'])->format('Y-m-d');
                    $filePath = storage_path('app/public/' . $data['excel_file']);
                    Excel::import(new BillImport(
                        $data['building_id'],
                        $data['flat_id'],
                        $month
                    ), $filePath);
                }),

            ExportAction::make()->exports([
                ExcelExport::make()
                    ->withColumns([
                        Column::make('type'),
                        Column::make('amount'),
                        Column::make('due_date'),
                        Column::make('status'),
                    ])
                    ->modifyQueryUsing(function ($query) {
                        return Bill::query()
                            ->whereIn('type', ['BTU', 'lpg', 'Telecommunication', 'DEWA'])
                            ->orderByRaw("CASE
                                WHEN type = 'BTU' THEN 1
                                WHEN type = 'lpg' THEN 2
                                WHEN type = 'Telecommunication' THEN 3
                                WHEN type = 'DEWA' THEN 4
                                END")
                            ->take(4)
                            ->getQuery()
                            ->fromSub(function ($query) {
                                $query->selectRaw("
                                    'BTU' as type, '' as amount, '' as due_date, '' as status
                                    UNION ALL
                                    SELECT 'lpg', '', '', ''
                                    UNION ALL
                                    SELECT 'Telecommunication', '', '', ''
                                    UNION ALL
                                    SELECT 'DEWA', '', '', ''
                                ");
                            }, 'sample_data');
                    }),
            ])->label('Download sample file'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'BTU'               => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'BTU')),
            'DEWA'              => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'DEWA')),
            'Telecommunication' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'Telecommunication')),
            'lpg'               => Tab::make()
                ->label('LPG')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'lpg')),
        ];
    }
}
