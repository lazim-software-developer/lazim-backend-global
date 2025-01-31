<?php

namespace App\Filament\Resources\Building;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\City;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Forms\Components\MarkdownEditor;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use App\Filament\Resources\BuildingResource\RelationManagers\VendorRelationManager;
use App\Filament\Resources\BuildingResource\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\FloorsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\MeetingsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\IncidentsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\BuildingvendorRelationManager;
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
                            'buildings',
                            'property_group_id',
                            fn(?Model $record) => $record,
                        ),

                    TextInput::make('address_line1')
                        ->rules(['required','max:500', 'string'])
                        ->required()
                        ->label('Address Line 1')
                        ->placeholder('Address line 1'),

                    TextInput::make('address_line2')
                        ->rules(['required','max:500', 'string'])
                        ->nullable()
                        ->label('Address line 2')
                        ->placeholder('Address Line 2'),

                    Hidden::make('owner_association_id')
                        ->default(auth()->user()?->owner_association_id),
                    Hidden::make('created_by')
                    ->default(auth()->user()?->id),
                    Hidden::make('updated_by')
                    ->default(auth()->user()?->id),

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

                    Select::make('city_id')
                        ->label('City') // Add or change the label
                        ->native(false)
                        ->required()
                        ->rules([
                            'required'
                        ])
                        ->options(function (callable $get) {

                            return City::pluck('name', 'id');
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
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('property_group_id')
                    ->label('Property group ID')
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
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            // BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            FloorsRelationManager::class,
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
