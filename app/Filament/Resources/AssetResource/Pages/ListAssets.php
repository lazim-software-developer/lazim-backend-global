<?php

namespace App\Filament\Resources\AssetResource\Pages;

use DB;
use Filament\Actions;
use App\Filament\Resources\AssetResource;
use App\Imports\AssetsListImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\Master\Service;
use Barryvdh\DomPDF\Facade\Pdf;
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

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;
    protected static ?string $title = 'Assets';
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')
        ->where('owner_association_id',auth()->user()?->owner_association_id)->pluck('building_id');
        if(auth()->user()?->role?->name === 'Property Manager'){
            return parent::getTableQuery()->whereIn('building_id', $buildingIds);
        }
        // if(Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager'){
        //     return parent::getTableQuery();
        // }
        elseif(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id',Filament::getTenant()->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportAction::make()->exports([
                ExcelExport::make()->withColumns([
                    Column::make('name')->heading('asset_name'),
                    Column::make('location'),
                    Column::make('floor'),
                    Column::make('division'),
                    Column::make('discipline'),
                    Column::make('frequency_of_service'),
                    Column::make('description'),
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
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->options(function () {
                                return Service::where('type', 'vendor_service')->where('active', 1)->pluck('name', 'id');
                            })
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Service'),
                        FileUpload::make('excel_file')
                        ->label('Assets Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required(),
                    ])
                    ->action(function (array $data) {
                    $buildingId= $data['building_id'];
                    $serviceId = $data['service_id'];
                    $filePath = $data['excel_file']; // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // Now import using the file path
                    Excel::import(new AssetsListImport( $buildingId, $serviceId), $fullPath);

                }),

                Action::make('QR Codes')->label('Download QR Codes')
                ->action(function(){
                    $data = Parent::getTableQuery()->get();

                    $pdf = Pdf::loadView('filament.custom.asset-fetch-data', compact('data'));
                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'Qr Codes.pdf'
                        );
                })

        ];
    }
}
