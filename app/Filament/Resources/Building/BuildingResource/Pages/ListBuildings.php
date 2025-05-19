<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Jobs\FetchBuildingsJob;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Imports\PropertyManagerBuildingsImport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use App\Filament\Resources\Building\BuildingResource;

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
            Action::make('Sync Buildings from Mollak')
            ->label('Sync Buildings from Mollak')
            ->icon('heroicon-o-information-circle')
            ->disabled(function (): bool {
                // Get the latest record for this user
                $lastSync = DB::table('mollak_api_call_histories')
                    ->where('module', 'Building')
                    ->where('job_name', 'FetchBuildingsJob')
                    ->where('user_id', auth()->user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
                
                // If no record exists, enable the button (return false for disabled)
                if (!$lastSync) {
                    return false;
                }
                
                // If record exists, check if it's less than 30 minutes old
                return now()->diffInMinutes(Carbon::parse($lastSync->created_at)) < 30;
            })
            ->extraAttributes(function () {
                // Get the last sync time from database
                $lastSync = DB::table('mollak_api_call_histories')->where('module', 'Building')->where('job_name', 'FetchBuildingsJob')->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
                
                // Default value if no sync history exists
                $lastSyncDisplay = 'Never synced';
                $lastSyncTime = now()->format('Y-m-d H:i:s');
                
                if ($lastSync) {
                    $lastSyncTime = $lastSync->created_at;
                    
                    // Format the display text based on time difference
                    $diffInMinutes = now()->diffInMinutes($lastSyncTime);
                    if ($diffInMinutes < 60) {
                        $lastSyncDisplay = $diffInMinutes . ' minutes ago';
                    } else {
                        $diffInHours = now()->diffInHours($lastSyncTime);
                        if ($diffInHours < 24) {
                            $lastSyncDisplay = $diffInHours . ' hours ago';
                        } else {
                            $lastSyncDisplay = Carbon::parse($lastSyncTime)->format('Y-m-d H:i:s');
                        }
                    }
                }
                
                return [
                    'title' => 'Last Sync: ' . $lastSyncDisplay,
                    'class' => 'relative',
                    'x-data' => '{
                        lastSync: "' . $lastSyncDisplay . '",
                        init() {
                            $el.innerHTML = "Sync Buildings from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                        }
                    }'
                ];
            })
            ->visible(function () {
                $auth_user = auth()->user();
                $role      = Role::where('id', $auth_user->role_id)->first()?->name;
        
                if ($role === 'Admin' || $role === 'OA') {
                    return true;
                }
            })
            ->action(function () {
                $ownerAssociation = OwnerAssociation::where('id', auth()->user()?->owner_association_id)->first();
                if (!empty($ownerAssociation->mollak_id)) {
                FetchBuildingsJob::dispatch($ownerAssociation, 'Manual');
                DB::table('mollak_api_call_histories')->insert([
                    'api_url'     => '/sync/managementcompany/' . $ownerAssociation->mollak_id . '/propertygroups',
                    'module'      => 'Building',
                    'job_name'    => 'FetchBuildingsJob',
                    'user_id'     => auth()->user()->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                Notification::make()
                    ->title('Fetching buildings from Mollak is in progress. Once synced, it will be visible in the list')
                    ->success()
                    ->send();
                }else{
                    Notification::make()
                    ->title('This Owner Association is not found in Mollak')
                    ->warning()
                    ->send();
                }
            }),
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
                            Column::make('building_type*'),
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
