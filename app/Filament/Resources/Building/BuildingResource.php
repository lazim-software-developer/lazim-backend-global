<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\BuildingResource\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\BuildingResource\RelationManagers\VendorRelationManager;
use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\BuildingserviceRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\BuildingvendorRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\EmergencyNumbersRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\FloorsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\IncidentsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\MeetingsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\OfferPromotionsRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\OwnercommitteesRelationManager;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\RuleregulationsRelationManager;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Unique;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Filament\Forms\Components\Fieldset;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Master\Role;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup       = 'Property Management';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $modelLabel            = 'Buildings';
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
                        ->disabled(function () {
                            if (auth()->user()->role->name !== 'Admin') {
                                return true;
                            }
                        })
                        ->placeholder('Name'),

                    TextInput::make('property_group_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->disabled(function () {
                            if (auth()->user()->role->name !== 'Admin') {
                                return true;
                            }
                        })
                        ->placeholder('Property Group Id')
                        ->unique(
                            'buildings',
                            'property_group_id',
                            fn(?Model $record) => $record,
                        ),

                    TextInput::make('address_line1')
                        ->rules(['max:500', 'string'])
                        ->required()
                        ->placeholder('Address Line1'),

                    TextInput::make('address_line2')
                        ->rules(['max:500', 'string'])
                        ->nullable()
                        ->placeholder('Address Line2'),
                    Hidden::make('owner_association_id')
                        ->default(auth()->user()?->owner_association_id),

                    TextInput::make('area')
                        ->rules(['max:100', 'string'])
                        ->required()
                        ->placeholder('Area'),

                    // Select::make('city_id')
                    //     ->rules(['exists:cities,id'])
                    //     ->preload()
                    //     ->relationship('cities', 'name')
                    //     ->searchable()
                    //     ->placeholder('NA'),
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
                        ->label('About'),
                    FileUpload::make('cover_photo')
                        ->disk('s3')
                        ->rules(['file', 'mimes:jpeg,jpg,png', function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value->getSize() / 1024 > 2048) {
                                    $fail('The cover Photo field must not be greater than 2MB.');
                                }
                            };
                        }])
                        ->directory('dev')
                        ->image()
                        ->maxSize(2048)
                        ->label('Cover Photo'),
                    TextInput::make('floors')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(999)
                        ->disabled(function (?Model $record) {
                            if ($record?->floors == null) {
                                return false;
                            }
                            return true;
                        })
                        ->placeholder('Floors')
                        ->label('Floor'),

                    Toggle::make('allow_postupload')
                        ->rules(['boolean'])
                        ->label('Allow post-upload'),
                    Toggle::make('show_inhouse_services')
                        ->rules(['boolean'])
                        ->label('Show Personal services')
                        ->hiddenOn('create'),

                // TextInput::make('lat')
                //     ->rules(['numeric'])
                //     ->placeholder('Lat'),

                // TextInput::make('lng')
                //     ->rules(['numeric'])
                //     ->placeholder('Lng'),

                // TextInput::make('description')
                //     ->rules(['max:255', 'string'])
                //     ->placeholder('Description'),

                Fieldset::make('Location')
                ->columns(1)
                ->visible(Role::where('id',auth()->user()->role_id)->first()->name == 'Property Manager')
                    ->schema([

                        Geocomplete::make('search')
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state == null) {
                                $set('lat', null);
                                $set('lng', null);
                                $set('pincode', null);
                            }
                        })
                            // ->label('Address')
                            ->placeholder('Enter location')
                            ->maxLength(256)
                            ->updateLatLng(true)
                            ->reactive()
                            ->types(['establishment'])
                            // ->countries(['IN'])
                            // ->Regex('/^[^!@#$]*$/')
                            ->validationMessages([
                                // 'Regex'     => 'Enter valid search location',
                                'countries' => 'International places not allowed',
                            ])
                            ->required()
                            ->live(),

                        Grid::make(['default' => 2])
                        ->columns(2)
                        ->schema([
                            TextInput::make('lat')
                            ->extraAttributes([
                                'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;'
                            ])
                            // ->hidden()
                            ->label('Latitude')
                            ->required()
                            ->rules(['max:255'])
                            ->placeholder('Lat')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('location', [
                                    'lat' => floatVal($state),
                                    'lng' => floatVal($get('lng')),
                                ]);
                            })
                                ->readOnly()
                                // ->disabled(function (callable $get) {
                                // if ($get('Search') == true) {
                                //     return false;
                                // }
                                // return true;
                                // })
                                ->lazy(),

                            TextInput::make('lng')
                            ->extraAttributes([
                                'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;'
                            ])
                            // ->hidden()
                            ->label('Longitude')
                            ->required()
                            ->rules(['max:255'])
                            ->nullable()
                            ->placeholder('Long')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('location', [
                                    'lat' => floatval($get('lat')),
                                    'lng' => floatVal($state),
                                ]);
                                $location = $get('location');
                            })
                                ->readonly()
                                //     ->disabled(function (callable $get) {
                                //     if ($get('Search') == true) {
                                //         return false;
                                //     }
                                //     return true;
                                // })
                                ->lazy(),
                        ]),

                        Map::make('location')
                        ->autocomplete('search')
                        ->autocompleteReverse(true)
                        ->mapControls([
                            'mapTypeControl'    => true,
                            'scaleControl'      => true,
                            'streetViewControl' => true,
                            'rotateControl'     => true,
                            'fullscreenControl' => true,
                            'searchBoxControl'  => false, // creates geocomplete field inside map
                            'zoomControl' => false,
                        ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $set('lat', $state['lat']);
                                $set('lng', $state['lng']);
                            })
                            ->height(fn() => '400px')
                            ->defaultZoom(15)
                            ->reverseGeocode([
                                'street' => '%n %S',
                                'city'   => '%L',
                                'state'  => '%A1',
                                'zip'    => '%z',
                            ])
                            ->draggable()
                            ->clickable(true)
                            ->geolocate()
                            ->geolocateLabel('Get Location')
                        // ->geolocateOnLoad(true, false)
                        ,
                    ]),
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
                        // try {
                        $budgetPeriod = $data['budget_period'];
                        $filePath     = $data['excel_file'];
                        $fullPath     = storage_path('app/' . $filePath);

                        if (!file_exists($fullPath)) {
                            Log::error("File not found at path: ", [$fullPath]);
                        }

                        // Now import using the file path
                        Excel::import(new BudgetImport($budgetPeriod, $record->id), $fullPath); // Notify user of success

                        // } catch (\Exception $e) {
                        //     // Log::error('Error during file import: ' . $e->getMessage());
                        //     Notification::make()
                        //     ->title($e->getMessage())
                        //     ->danger()
                        //     ->send();
                        // }
                    }),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            VendorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'    => Pages\ListBuildings::route('/'),
            'create'   => Pages\CreateBuilding::route('/create'),
            'edit'     => Pages\EditBuilding::route('/{record}/edit'),
            'services' => Pages\ShowServices::route('services'),
        ];
    }
}
