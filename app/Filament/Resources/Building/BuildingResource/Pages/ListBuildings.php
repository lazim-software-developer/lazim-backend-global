<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use App\Imports\PropertyManagerBuildingsImport;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;
    protected function getTableQuery(): Builder
    {
        $buildingIds = DB::table('building_owner_association')->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('building_id');
        if (auth()->user()?->role?->name === 'Property Manager') {
            return parent::getTableQuery()->whereIn('id', $buildingIds);
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        return parent::getTableQuery();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Building')
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    if ($role === 'Admin') {
                        return true;
                    }
                }),

            Action::make('Upload Buildings')
                ->visible(auth()->user()?->role?->name === 'Property Manager')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('Upload File')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('budget_imports'),
                ])
                ->action(function ($record, array $data, $livewire) {
                    $filePath = $data['excel_file'];
                    $fullPath = storage_path('app/' . $filePath);
                    $oaId     = auth()->user()->owner_association_id;

                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                        return;
                    }

                    Excel::import(new PropertyManagerBuildingsImport($oaId), $fullPath);
                }),

            // ActionGroup::make([
                ExportAction::make('exporttemplate')
                    ->exports([
                        ExcelExport::make()
                            ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                            ->withColumns([
                                Column::make('name'),
                                Column::make('building_type'),
                                Column::make('property_group_id'),
                                Column::make('address_line1'),
                                Column::make('area'),
                                Column::make('floors'),
                                Column::make('parking_count'),
                                Column::make('from'),
                                Column::make('to'),
                            ]),
                    ])
                    ->label('Download sample file'),
            // ])->tooltip('Download sample file'),
        ];
    }
}
