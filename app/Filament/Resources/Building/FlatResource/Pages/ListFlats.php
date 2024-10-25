<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use App\Imports\FlatImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListFlats extends ListRecords
{
    protected static string $resource = FlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(in_array(auth()->user()->role->name, ['Admin','Property Manager'])),
            Action::make('feature')
                ->label('Upload Units') // Set a label for your action
                ->visible(in_array(auth()->user()->role->name, ['Admin','Property Manager']))
                ->form([
                      Select::make('owner_association_id')
                      ->options(function(){
                          return OwnerAssociation::where('role','Property Manager')->pluck('name','id');
                        })
                        ->visible(auth()->user()->role->name === 'Admin')
                        ->required()
                        ->live()
                        ->preload()
                        ->searchable()
                        ->label('Select Property Manager'),
                    Select::make('building_id')
                        ->options(function(Get $get){
                            $buildings = DB::table('building_owner_association')->where('owner_association_id',$get('owner_association_id') ?? auth()->user()->owner_association_id)->pluck('building_id');
                            return Building::whereIn('id',$buildings)->pluck('name','id');
                        })
                        ->required()
                        ->live()
                        ->preload()
                        ->searchable()
                        ->label('Select Building'),
                    FileUpload::make('excel_file')
                        ->label('Upload File')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required()
                        ->disk('local') // or your preferred disk
                        ->directory('budget_imports'), // or your preferred directory
                ])
                ->action(function ($record, array $data, $livewire) {

                    $filePath = $data['excel_file'];
                    $fullPath = storage_path('app/' . $filePath);
                    $oaId     = $data['owner_association_id'] ?? auth()->user()->owner_association_id;
                    $buildingId     = $data['building_id'];

                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                    }

                    // Now import using the file path
                    Excel::import(new FlatImport($oaId,$buildingId), $fullPath); // Notify user of success
                }),
            ExportAction::make('exporttemplate')->exports([
                ExcelExport::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                    ->withColumns([
                        Column::make('unit_number'),
                        Column::make('property_type'),
                        // Column::make('mollak_property_id'),
                        Column::make('suit_area'),
                        Column::make('actual_area'),
                        Column::make('balcony_area'),
                        // Column::make('applicable_area'),
                        Column::make('parking_count'),
                        Column::make('plot_number'),
                        Column::make('makhani_number'),
                        Column::make('dewa_number'),
                        Column::make('btu/etisalat_number'),
                        Column::make('btu/ac_number'),
                    ]),
            ])
            ->visible(in_array(auth()->user()->role->name, ['Admin','Property Manager']))
            ->label('Download sample format file'),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $building  = Building::all()->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id')->toArray();
        $buildings = DB::table('building_owner_association')->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('building_id');
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery();
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            return parent::getTableQuery()->whereIn('building_id', $buildings);
        }
        return parent::getTableQuery()->whereIn('building_id', $building);
    }
}
