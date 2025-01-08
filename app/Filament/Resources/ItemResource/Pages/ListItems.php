<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Imports\ItemsListImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use DB;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportAction::make()->exports([
                ExcelExport::make()->withColumns([
                    Column::make('name')->heading('Item Name'),
                    Column::make('quantity')->heading('Quantity'),
                    Column::make('description')->heading('Description'),
                ])
                ->modifyQueryUsing(fn ($query) => $query->where('id', 0)),
            ])->label('Download sample file'),
            Action::make('upload')
                    ->slideOver()
                    ->color("primary")
                    ->form([
                        Select::make('building_id')
                        ->required()
                        ->preload()
                        ->relationship('building', 'name')
                        ->options(function () {
                            $oaId = auth()->user()?->owner_association_id;
                            // dd($tenants);
                            if (auth()->user()->role->name == 'Property Manager') {
                                $buildingIds = DB::table('building_owner_association')
                                    ->where('owner_association_id', auth()->user()->owner_association_id)
                                    ->where('active', true)
                                    ->pluck('building_id');

                                return Building::whereIn('id', $buildingIds)
                                    ->pluck('name', 'id');

                            }

                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->label('Building Name'),
                        FileUpload::make('excel_file')
                        ->label('Items Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required(),
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    $filePath = $data['excel_file']; // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // Now import using the file path
                    Excel::import(new ItemsListImport( $buildingId), $fullPath);

                }),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');
        if (auth()->user()?->role?->name === 'Property Manager') {
            return parent::getTableQuery()->whereIn('building_id', $buildingIds);
        }

        elseif(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id', Filament::getTenant()?->id);
    }
}
