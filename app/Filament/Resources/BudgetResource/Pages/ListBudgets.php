<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Imports\MyBudgetImport;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource;
use App\Models\Master\Role;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\DB;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;
    protected static ?string $title = 'Budgets';
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        $buildings_id = DB::table('building_owner_association')->where('owner_association_id',Filament::getTenant()->id)->where('active', true)->pluck('building_id');
        return parent::getTableQuery()->whereIn('building_id', $buildings_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('feature')
                ->label('Upload Budget') // Set a label for your action
                ->modalHeading('Upload Budget for Period') // Modal heading
                ->form([
                    Grid::make(2)
                    ->schema([
                        Select::make('building_id')
                        ->options(function(){
                            if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                return Building::all()->pluck('name', 'id');
                            }
                            else{
                                return Building::where('owner_association_id', Filament::getTenant()->id)
                                ->pluck('name', 'id');
                            } 
                        })
                        ->preload()
                        ->searchable()
                        ->label('Select Building')
                        ->required(),
                    Select::make('budget_period')
                        ->label('Select Budget Period')
                        ->options([
                            'Jan 2024 - Dec 2024' => '2024',
                            'Jan 2023 - Dec 2023' => '2023',
                            'Jan 2022 - Dec 2022' => '2022',
                            'Jan 2021 - Dec 2021' => '2021',
                            'Jan 2020 - Dec 2020' => '2020',
                            'Jan 2019 - Dec 2019' => '2019',
                            'Jan 2018 - Dec 2018' => '2018',
                        ])
                        ->searchable()
                        ->required(),
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
                ])
                ->action(function ($record, array $data, $livewire) {
                    // try {
                    $budgetPeriod = $data['budget_period'];
                    $filePath = $data['excel_file'];
                    $fullPath = storage_path('app/' . $filePath);

                    if (!file_exists($fullPath)) {
                        Log::error("File not found at path: ", [$fullPath]);
                    }

                    // Now import using the file path
                    Excel::import(new BudgetImport($budgetPeriod, $data['building_id']), $fullPath); // Notify user of success

                    // } catch (\Exception $e) {
                    //     // Log::error('Error during file import: ' . $e->getMessage());
                    //     Notification::make()
                    //     ->title($e->getMessage())
                    //     ->danger()
                    //     ->send();
                    // }
                }),
        ];
    }
}
