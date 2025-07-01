<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use App\Imports\MyBudgetImport;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource;
use EightyNine\ExcelImport\ExcelImportAction;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;
    protected static ?string $title = 'Budgets';
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery();
        }
        $buildings_id = DB::table('building_owner_association')
            ->where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()?->owner_association_id)
            ->where('active', true)->pluck('building_id');
        return parent::getTableQuery()->whereIn('building_id', $buildings_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Action::make('feature')
                ->label('Upload Budget') // Set a label for your action
                ->modalHeading('Upload Budget for Period') // Modal heading
                ->form([
                    View::make('filament.components.sample-download-link')
                        ->view('filament.components.budget-sample-file-download'),
                    Grid::make(2)
                        ->schema([

                            Select::make('building_id')
                                ->options(function () {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        return Building::all()->pluck('name', 'id');
                                    } else {
                                        $buildings_id = DB::table('building_owner_association')
                                            ->where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()?->owner_association_id)
                                            ->where('active', true)->pluck('building_id');
                                        return Building::whereIn('id', $buildings_id)
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
                                    'Jan 2025 - Dec 2025' => '2025',
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
