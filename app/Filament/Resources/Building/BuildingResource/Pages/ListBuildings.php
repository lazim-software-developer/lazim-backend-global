<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use App\Imports\PropertyManagerBuildingsImport;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
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

    public function getTabs(): array
    {
        if (auth()->user()?->role?->name !== 'Property Manager') {
            return [];
        }

        return [
            'active'   => Tab::make('Attached Buildings')
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->icon('heroicon-o-check-circle'),
            'inactive' => Tab::make('Detached Buildings')
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->icon('heroicon-o-x-circle'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (auth()->user()?->role?->name === 'Property Manager') {
            $buildingIds = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()?->owner_association_id)
                ->where('active', $this->activeTab === 'active' ? 1 : 0)
                ->pluck('building_id');

            return $query->whereIn('id', $buildingIds);
        }

        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return $query->where('owner_association_id', auth()->user()?->owner_association_id);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Building')
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    if ($role === 'Admin' || $role === 'Property Manager') {
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
                ->visible(auth()->user()?->role?->name === 'Property Manager')
                ->exports([
                    ExcelExport::make()
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                        ->withColumns([
                            Column::make('name*'),
                            Column::make('building_type'),
                            Column::make('property_group_id*'),
                            Column::make('address_line1*'),
                            Column::make('area'),
                            Column::make('floors'),
                            Column::make('parking_count'),
                            Column::make('contract_start_date')->heading('Contract Start Date*'),
                            Column::make('contract_end_date')->heading('Contract End Date*'),
                        ]),
                ])
                ->label('Download sample file'),
            // ])->tooltip('Download sample file'),
        ];
    }
}
