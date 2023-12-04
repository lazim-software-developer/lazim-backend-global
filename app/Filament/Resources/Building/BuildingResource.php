<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Property Management';
    protected static bool $shouldRegisterNavigation = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),

                    TextInput::make('property_group_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Property Group Id')
                        ->unique(
                            'buildings',
                            'property_group_id',
                            fn (?Model $record) => $record
                        ),

                    TextInput::make('address_line1')
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->placeholder('Address Line1'),

                    TextInput::make('address_line2')
                        ->rules(['max:255', 'string'])
                        ->nullable()
                        ->placeholder('Address Line2'),
                    Hidden::make('owner_association_id')
                        ->default(auth()->user()->owner_association_id),

                    // TextInput::make('area')
                    //     ->rules(['max:50', 'string'])
                    //     ->required()
                    //     ->placeholder('Area'),

                    Select::make('city_id')
                        ->rules(['exists:cities,id'])
                        ->required()
                        ->preload()
                        ->relationship('cities', 'name')
                        ->searchable()
                        ->placeholder('City'),

                    // TextInput::make('lat')
                    //     ->rules(['numeric'])
                    //     ->placeholder('Lat'),

                    // TextInput::make('lng')
                    //     ->rules(['numeric'])
                    //     ->placeholder('Lng'),

                    // TextInput::make('description')
                    //     ->rules(['max:255', 'string'])
                    //     ->placeholder('Description'),

                    // TextInput::make('floors')
                    //     ->rules(['numeric'])
                    //     ->required()
                    //     ->numeric()
                    //     ->placeholder('Floors')

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
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('property_group_id')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                // Tables\Columns\TextColumn::make('address_line1')
                //     ->toggleable()
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
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
                Action::make('feature')
                    ->label('Upload Budget') // Set a label for your action
                    ->modalHeading('Upload Budget for Period') // Modal heading
                    ->form([
                        Forms\Components\Select::make('budget_period')
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
                            ->required(),
                        Forms\Components\FileUpload::make('excel_file')
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
                        try {
                            $budgetPeriod = $data['budget_period'];
                            $filePath = $data['excel_file'];
                            $fullPath = storage_path('app/' . $filePath);
                            Log::info("Full path: ", [$fullPath]);

                            if (!file_exists($fullPath)) {
                                Log::error("File not found at path: ", [$fullPath]);
                            }

                            // Now import using the file path
                            Excel::import(new BudgetImport($budgetPeriod, $record->id), $fullPath); // Notify user of success
                            Notification::make()
                            ->title("Budget file imported successfully.")
                            ->success()
                            ->send();
                        } catch (\Exception $e) {
                            Log::error('Error during file import: ' . $e->getMessage());

                            // Notify user of failure
                            Session::flash('notify', ['type' => 'danger', 'message' => 'Failed to import budget file.']);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            // BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            BuildingResource\RelationManagers\ComplaintsRelationManager::class,
            // BuildingResource\RelationManagers\ServicesRelationManager::class,
            BuildingResource\RelationManagers\ServiceRelationManager::class,
            // BuildingResource\RelationManagers\DocumentsRelationManager::class,
            BuildingResource\RelationManagers\FacilitiesRelationManager::class,
            BuildingResource\RelationManagers\FlatsRelationManager::class,
            BuildingResource\RelationManagers\VendorRelationManager::class,
            BuildingResource\RelationManagers\AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
