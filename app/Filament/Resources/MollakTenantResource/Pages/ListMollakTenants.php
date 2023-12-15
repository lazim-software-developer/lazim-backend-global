<?php

namespace App\Filament\Resources\MollakTenantResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Imports\MyClientImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CoolingAccountImport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Filament\Resources\MollakTenantResource;

class ListMollakTenants extends ListRecords
{
    protected static string $resource = MollakTenantResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         //Actions\CreateAction::make(),
    //         // ExcelImportAction::make()
    //         //     ->color("primary"),
    //         ExcelImportAction::make()
    //         ->slideOver()
    //         ->color("primary")
    //         ->use(MyClientImport::class),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') 
        {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('building_id',Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id'));
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
                        ->relationship('building', 'name')
                        ->options(function () {
                            if (DB::table('roles')->where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id');
                            }
                        })
                        ->searchable()
                        ->label('Building Name'),
                    FileUpload::make('excel_file')
                        ->label('Mollak Tenant Excel Data')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                            'application/vnd.ms-excel', // for .xls
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $buildingId = $data['building_id'];
                    $filePath = $data['excel_file']; // This is likely just a file path or name
                    // Assuming the file is stored in the local disk in a 'budget_imports' directory
                    $fullPath = storage_path('app/public/' . $filePath);
                    Log::info("Full path: ", [$fullPath]);
                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        // Handle the error appropriately
                    }

                    // Now import using the file path
                    Excel::import(new MyClientImport($buildingId), $fullPath);

                }),
        ];
    }
}
