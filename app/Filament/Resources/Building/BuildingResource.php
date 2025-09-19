<?php

namespace App\Filament\Resources\Building;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\City;
use App\Models\Master\Role;
use App\Models\Master\State;
use App\Models\Master\Country;
use Illuminate\Validation\Rule;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Imports\BuildingImport;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\FileUpload;
use App\Filament\Exports\BuildingDataExport;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Forms\Components\MarkdownEditor;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use App\Filament\Resources\BuildingResource\RelationManagers\VendorRelationManager;
use App\Filament\Resources\BuildingResource\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\FloorsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\MeetingsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\IncidentsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\BuildingvendorRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\LocationQrCodeRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\BuildingserviceRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\OfferPromotionsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\OwnercommitteesRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\RuleregulationsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\EmergencyNumbersRelationManager;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Property Management';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $modelLabel = 'Buildings';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->rules([
                            'required'
                        ])
                        // ->disabled()
                        ->placeholder('Name'),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->placeholder('Slug')
                        ->rules([
                            'required',
                            'string',
                            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                            'min:4',
                            'max:30'
                        ])
                        ->validationMessages([
                            'regex' => 'Slug format is Invalid. It can only accept Lowercase letters, Numbers and hyphen'
                        ])
                        ->unique('buildings', 'slug', ignoreRecord: true)
                        ->disabled(function (callable $get) {
                            // Get the current operation (create or edit)
                            $isCreate = !$get('id'); // if id exists, it's edit operation

                            // If it's create operation, return false (not disabled)
                            if ($isCreate) {
                                return false;
                            }

                            // For edit operation, apply your existing logic
                            return Role::where('id', auth()->user()->role_id)
                                ->first()->name != 'Admin' &&
                                DB::table('buildings')
                                ->where('slug', $get('slug'))
                                ->exists();
                        }),
                    TextInput::make('property_group_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->rules([
                            'required'
                        ])
                        // ->disabled()
                        ->placeholder('Property Group ID')
                        ->label('Property Group ID')
                        ->rules([
                            'required'
                        ])
                        ->unique(
                            table: 'buildings',
                            column: 'property_group_id',
                            ignorable: fn($record) => $record
                        ),

                    TextInput::make('address_line1')
                        ->rules(['required', 'max:500', 'string'])
                        ->required()
                        ->label('Address Line 1')
                        ->placeholder('Address line 1'),

                    TextInput::make('address_line2')
                        ->rules(['max:500', 'string'])
                        ->nullable()
                        ->label('Address line 2')
                        ->placeholder('Address Line 2'),

                    Hidden::make('owner_association_id')
                        ->default(auth()->user()?->owner_association_id),
                    Hidden::make('created_by')
                        ->default(auth()->user()?->id),
                    Hidden::make('updated_by')
                        ->default(auth()->user()?->id),
                    Hidden::make('resource')
                        ->default('Lazim'),
                    TextInput::make('area')
                        ->rules(['max:100', 'string'])
                        ->required()
                        ->rules([
                            'required'
                        ])
                        ->placeholder('Area'),
                    TextInput::make('floors')
                        ->required()
                        ->rules([
                            'required'
                        ])
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(999)
                        ->disabled(function ($record) {
                            if ($record?->floors == null) {
                                return false;
                            }
                            return true;
                        })

                        ->placeholder('Floors')
                        ->label('Floor'),

                    Select::make('country_id')
                        ->label('Country')
                        ->native(false)
                        ->required()
                        ->rules(['required'])
                        ->options(function () {
                            return Country::pluck('name', 'id');
                        })
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            // When country changes, clear both state and city
                            $set('state_id', null);
                            $set('city_id', null);
                        })
                        ->searchable(),

                    Select::make('state_id')
                        ->label('State')
                        ->native(false)
                        ->required()
                        ->rules(['required'])
                        ->options(function (callable $get) {
                            if ($get('country_id')) {
                                return State::where('country_id', $get('country_id'))->pluck('name', 'id');
                            }
                            return [];
                        })
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            // When state changes, only clear city
                            $set('city_id', null);
                        })
                        ->searchable(),

                    Select::make('city_id')
                        ->label('City')
                        ->native(false)
                        ->required()
                        ->rules(['required'])
                        ->options(function (callable $get) {
                            if ($get('state_id')) {
                                return City::where('state_id', $get('state_id'))->pluck('name', 'id');
                            }
                            return [];
                        })
                        ->searchable(),

                    Toggle::make('allow_postupload')
                        ->columnStart([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                        ])
                        ->rules(['boolean'])
                        ->label('Allow post-upload'),

                    Toggle::make('show_inhouse_services')
                        ->columnStart([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                        ])
                        ->rules(['boolean'])
                        ->label('Show personal services'),

                    Toggle::make('status')
                        ->rules(['boolean'])
                        ->default(true) // Sets the default value to true (active)
                        ->label('Status'),

                    MarkdownEditor::make('description')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'undo',
                        ])
                        ->label('About')
                        ->columnSpanFull(),
                    FileUpload::make('cover_photo')
                        ->disk('s3')
                        ->rules([
                            'file',
                            'mimes:jpeg,jpg,png',
                            function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if ($value->getSize() / 1024 > 2048) {
                                        $fail('The cover Photo field must not be greater than 2MB.');
                                    }
                                };
                            },
                        ])
                        ->directory('dev')
                        ->image()
                        ->maxSize(2048)
                        ->label('Cover Photo'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('property_group_id')
                    ->label('Property group ID')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('resource')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                // Tables\Columns\TextColumn::make('address_line2')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('area')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('cities.name')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('lat')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('lng')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('description')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                // Tables\Columns\TextColumn::make('floors')
                //     ->toggleable()
                //     ->default('NA')
                //     ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                // Action::make('feature')
                //     ->label('Upload Budget') // Set a label for your action
                //     ->modalHeading('Upload Budget for Period') // Modal heading
                //     ->form([
                //         Forms\Components\Select::make('budget_period')
                //             ->label('Select Budget Period')
                //             ->options([
                //                 'Jan 2024 - Dec 2024' => '2024',
                //                 'Jan 2023 - Dec 2023' => '2023',
                //                 'Jan 2022 - Dec 2022' => '2022',
                //                 'Jan 2021 - Dec 2021' => '2021',
                //                 'Jan 2020 - Dec 2020' => '2020',
                //                 'Jan 2019 - Dec 2019' => '2019',
                //                 'Jan 2018 - Dec 2018' => '2018',
                //             ])
                //             ->required(),
                //         Forms\Components\FileUpload::make('excel_file')
                //             ->label('Upload File')
                //             ->acceptedFileTypes([
                //                 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                //                 'application/vnd.ms-excel', // for .xls
                //             ])
                //             ->required()
                //             ->disk('local') // or your preferred disk
                //             ->directory('budget_imports'), // or your preferred directory
                //     ])
                //     ->action(function ($record, array $data, $livewire) {
                //         // try {
                //         $budgetPeriod = $data['budget_period'];
                //         $filePath = $data['excel_file'];
                //         $fullPath = storage_path('app/' . $filePath);

                //         if (!file_exists($fullPath)) {
                //             Log::error("File not found at path: ", [$fullPath]);
                //         }

                //         // Now import using the file path
                //         Excel::import(new BudgetImport($budgetPeriod, $record->id), $fullPath); // Notify user of success

                //         // } catch (\Exception $e) {
                //         //     // Log::error('Error during file import: ' . $e->getMessage());
                //         //     Notification::make()
                //         //     ->title($e->getMessage())
                //         //     ->danger()
                //         //     ->send();
                //         // }
                //     }),
                Action::make('delete')
                    ->button()
                    ->action(function ($record,) {
                        $record->delete();

                        Notification::make()
                            ->title('Building Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->withColumns([
                                Column::make('created_by')
                                    ->heading('Created By')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->CreatedBy->first_name . ' ' . $record->CreatedBy->last_name ?? 'N/A'
                                    ),
                                // Custom column using relationship
                                Column::make('owner_association_id')
                                    ->heading('Owner Association Name')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->ownerAssociationData->name ?? 'N/A'
                                    ),
                                Column::make('name')
                                    ->heading('Building Name'),
                                Column::make('floors')
                                    ->heading('Floors'),
                                Column::make('property_group_id')
                                    ->heading('Property Group ID')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->property_group_id ?? 'N/A'
                                    ),
                                Column::make('address_line1')
                                    ->heading('Address Line 1')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->address_line1 ?? 'N/A'
                                    ),
                                Column::make('address_line2')
                                    ->heading('Address Line 2')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->address_line2 ?? 'N/A'
                                    ),
                                Column::make('area')
                                    ->heading('Area')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->area ?? 'N/A'
                                    ),
                                Column::make('city_id')
                                    ->heading('City')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->cities->name ?? 'N/A'
                                    ),
                                // Formatted date with custom accessor
                                Column::make('created_at')
                                    ->heading('Created Date')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        $state ? $state->format('d/m/Y') : ''
                                    ),
                                Column::make('status')
                                    ->heading('Status')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->status == 1
                                            ? 'Active'
                                            : 'Inactive'
                                    ),

                                // Created by user info
                                // Column::make('created_by_name')
                                //     ->heading('Created By')
                                //     ->formatStateUsing(fn ($record) =>
                                //         $record->createdBy->name ?? 'System'
                                //     ),
                            ])
                            ->withFilename(date('Y-m-d') . '-buildings-report')
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                    ]),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Buildings')
                    ->form([
                        Section::make()
                            ->schema([
                                View::make('filament.components.sample-download-link')
                                    ->view('filament.components.sample-file-download'),
                                FileUpload::make('file')
                                    ->label('Choose CSV File')
                                    ->disk('local')
                                    ->directory('temp-imports')
                                    ->acceptedFileTypes([
                                        'text/csv',
                                        'text/plain',
                                        'application/csv',
                                    ])
                                    ->maxSize(5120)
                                    ->required()
                                    ->helperText('Upload your CSV file in the correct format')
                            ])
                    ])
                    ->action(function (array $data) {
                        try {
                            $import = new BuildingImport();
                            Excel::import($import, $data['file']);

                            $result = $import->getResultSummary();

                            if ($result['status'] === 200) {
                                // Generate detailed report
                                $report = "Import Report " . now()->format('Y-m-d H:i:s') . "\n\n";
                                $report .= "Successfully imported: {$result['imported']}\n";
                                $report .= "Skipped (already exists): {$result['skip']}\n";
                                $report .= "Errors: {$result['error']}\n\n";

                                // Add detailed error and skip information
                                foreach ($result['details'] as $detail) {
                                    $report .= "Row {$detail['row_number']}: {$detail['message']}\n";
                                    $report .= "Data: " . json_encode($detail['data']) . "\n\n";
                                }

                                // Save report
                                $filename = 'building-import-' . now()->format('Y-m-d-H-i-s') . '.txt';
                                $reportPath = 'import-reports/' . $filename;
                                Storage::disk('local')->put($reportPath, $report);
                            }
                            if ($result['status'] === 401) {
                                Notification::make()
                                    ->title('invalid File')
                                    ->body("{$result['error']}")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            } else {
                                // Show notification with results
                                Notification::make()
                                    ->title('Import Complete')
                                    ->body(
                                        collect([
                                            "Successfully imported: {$result['imported']}",
                                            "Skipped: {$result['skip']}",
                                            "Errors: {$result['error']}"
                                        ])->join("\n")
                                    )
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('download_report')
                                            ->label('Download Report')
                                            ->url(route('download.import.report', ['filename' => $filename]))
                                            ->openUrlInNewTab()
                                    ])
                                    ->success()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }

                        // Clean up temporary file
                        Storage::disk('local')->delete($data['file']);
                    })
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            // BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            FloorsRelationManager::class,
            LocationQrCodeRelationManager::class,
            RuleregulationsRelationManager::class,
            EmergencyNumbersRelationManager::class,
            OfferPromotionsRelationManager::class,
            OwnercommitteesRelationManager::class,
            MeetingsRelationManager::class,
            BuildingserviceRelationManager::class,
            BuildingResource\RelationManagers\ComplaintRelationManager::class,
            IncidentsRelationManager::class,
            BuildingResource\RelationManagers\ServiceRelationManager::class,
            // BuildingResource\RelationManagers\DocumentsRelationManager::class,
            BuildingResource\RelationManagers\FacilitiesRelationManager::class,
            BuildingResource\RelationManagers\FlatsRelationManager::class,
            // BuildingResource\RelationManagers\VendorRelationManager::class,
            BuildingvendorRelationManager::class,
            BuildingResource\RelationManagers\AssetsRelationManager::class,
            ContractsRelationManager::class,
            VendorRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
            'services' => Pages\ShowServices::route('services'),
        ];
    }
}
