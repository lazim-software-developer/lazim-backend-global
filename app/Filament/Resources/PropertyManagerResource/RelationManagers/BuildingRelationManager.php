<?php

namespace App\Filament\Resources\PropertyManagerResource\RelationManagers;

use App\Imports\BuildingImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BuildingRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    protected static ?string $recordTitleAttribute = 'name';

    // public function form(Forms\Form $form): Forms\Form
    // {
    //     return $form
    //         ->schema([
    //             Grid::make([
    //                 'sm' => 1,
    //                 'md' => 1,
    //                 'lg' => 1,
    //             ])->schema([
    //                 TextInput::make('name')
    //                     ->rules(['max:50', 'string'])
    //                     ->required()
    //                     ->disabled(function () {
    //                         if (auth()->user()->role->name !== 'Admin') {
    //                             return true;
    //                         }
    //                     })
    //                     ->placeholder('Name'),

    //                 TextInput::make('property_group_id')
    //                     ->rules(['max:50', 'string'])
    //                     ->required()
    //                     ->disabled(function () {
    //                         if (auth()->user()->role->name !== 'Admin') {
    //                             return true;
    //                         }
    //                     })
    //                     ->placeholder('Property Group Id')
    //                     ->unique(
    //                         'buildings',
    //                         'property_group_id',
    //                         fn(?Model $record) => $record,
    //                     ),

    //                 TextInput::make('address_line1')
    //                     ->rules(['max:500', 'string'])
    //                     ->required()
    //                     ->placeholder('Address Line1'),

    //                 TextInput::make('address_line2')
    //                     ->rules(['max:500', 'string'])
    //                     ->nullable()
    //                     ->placeholder('Address Line2'),
    //                 Hidden::make('owner_association_id')
    //                     ->default(auth()->user()?->owner_association_id),

    //                 TextInput::make('area')
    //                     ->rules(['max:100', 'string'])
    //                     ->required()
    //                     ->placeholder('Area'),

    //                 // Select::make('city_id')
    //                 //     ->rules(['exists:cities,id'])
    //                 //     ->preload()
    //                 //     ->relationship('cities', 'name')
    //                 //     ->searchable()
    //                 //     ->placeholder('NA'),
    //                 MarkdownEditor::make('description')
    //                     ->toolbarButtons([
    //                         'bold',
    //                         'bulletList',
    //                         'italic',
    //                         'link',
    //                         'orderedList',
    //                         'redo',
    //                         'undo',
    //                     ])
    //                     ->label('About'),
    //                 FileUpload::make('cover_photo')
    //                     ->disk('s3')
    //                     ->rules(['file', 'mimes:jpeg,jpg,png', function () {
    //                         return function (string $attribute, $value, Closure $fail) {
    //                             if ($value->getSize() / 1024 > 2048) {
    //                                 $fail('The cover Photo field must not be greater than 2MB.');
    //                             }
    //                         };
    //                     }])
    //                     ->directory('dev')
    //                     ->image()
    //                     ->maxSize(2048)
    //                     ->label('Cover Photo'),
    //                 TextInput::make('floors')
    //                     ->numeric()
    //                     ->minValue(1)
    //                     ->maxValue(999)
    //                     ->disabled(function (?Model $record) {
    //                         if ($record?->floors == null) {
    //                             return false;
    //                         }
    //                         return true;
    //                     })
    //                     ->placeholder('Floors')
    //                     ->label('Floor'),

    //                 Toggle::make('allow_postupload')
    //                     ->rules(['boolean'])
    //                     ->label('Allow post-upload'),
    //                 Toggle::make('show_inhouse_services')
    //                     ->rules(['boolean'])
    //                     ->label('Show Personal services')
    //                     ->hiddenOn('create'),

    //                 // TextInput::make('lat')
    //                 //     ->rules(['numeric'])
    //                 //     ->placeholder('Lat'),

    //                 // TextInput::make('lng')
    //                 //     ->rules(['numeric'])
    //                 //     ->placeholder('Lng'),

    //                 // TextInput::make('description')
    //                 //     ->rules(['max:255', 'string'])
    //                 //     ->placeholder('Description'),

    //                 Fieldset::make('Location')
    //                     ->columns(1)
    //                     ->visible(Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager')
    //                     ->schema([

    //                         Geocomplete::make('search')
    //                             ->afterStateUpdated(function ($state, Set $set) {
    //                                 if ($state == null) {
    //                                     $set('lat', null);
    //                                     $set('lng', null);
    //                                     $set('pincode', null);
    //                                 }
    //                             })
    //                         // ->label('Address')
    //                             ->placeholder('Enter location')
    //                             ->maxLength(256)
    //                             ->updateLatLng(true)
    //                             ->reactive()
    //                             ->types(['establishment'])
    //                         // ->countries(['IN'])
    //                         // ->Regex('/^[^!@#$]*$/')
    //                             ->validationMessages([
    //                                 // 'Regex'     => 'Enter valid search location',
    //                                 'countries' => 'International places not allowed',
    //                             ])
    //                             ->required()
    //                             ->live(),

    //                         Grid::make(['default' => 2])
    //                             ->columns(2)
    //                             ->schema([
    //                                 TextInput::make('lat')
    //                                     ->extraAttributes([
    //                                         'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;',
    //                                     ])
    //                                 // ->hidden()
    //                                     ->label('Latitude')
    //                                     ->required()
    //                                     ->rules(['max:255'])
    //                                     ->placeholder('Lat')
    //                                     ->reactive()
    //                                     ->afterStateUpdated(function ($state, callable $get, callable $set) {
    //                                         $set('location', [
    //                                             'lat' => floatVal($state),
    //                                             'lng' => floatVal($get('lng')),
    //                                         ]);
    //                                     })
    //                                     ->readOnly()
    //                                 // ->disabled(function (callable $get) {
    //                                 // if ($get('Search') == true) {
    //                                 //     return false;
    //                                 // }
    //                                 // return true;
    //                                 // })
    //                                     ->lazy(),

    //                                 TextInput::make('lng')
    //                                     ->extraAttributes([
    //                                         'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;',
    //                                     ])
    //                                 // ->hidden()
    //                                     ->label('Longitude')
    //                                     ->required()
    //                                     ->rules(['max:255'])
    //                                     ->nullable()
    //                                     ->placeholder('Long')
    //                                     ->reactive()
    //                                     ->afterStateUpdated(function ($state, callable $get, callable $set) {
    //                                         $set('location', [
    //                                             'lat' => floatval($get('lat')),
    //                                             'lng' => floatVal($state),
    //                                         ]);
    //                                         $location = $get('location');
    //                                     })
    //                                     ->readonly()
    //                                 //     ->disabled(function (callable $get) {
    //                                 //     if ($get('Search') == true) {
    //                                 //         return false;
    //                                 //     }
    //                                 //     return true;
    //                                 // })
    //                                     ->lazy(),
    //                             ]),

    //                         Map::make('location')
    //                             ->autocomplete('search')
    //                             ->autocompleteReverse(true)
    //                             ->mapControls([
    //                                 'mapTypeControl'    => true,
    //                                 'scaleControl'      => true,
    //                                 'streetViewControl' => true,
    //                                 'rotateControl'     => true,
    //                                 'fullscreenControl' => true,
    //                                 'searchBoxControl'  => false, // creates geocomplete field inside map
    //                                 'zoomControl' => false,
    //                             ])
    //                             ->reactive()
    //                             ->afterStateUpdated(function ($state, Set $set, Get $get) {
    //                                 $set('lat', $state['lat']);
    //                                 $set('lng', $state['lng']);
    //                             })
    //                             ->height(fn() => '400px')
    //                             ->defaultZoom(15)
    //                             ->reverseGeocode([
    //                                 'street' => '%n %S',
    //                                 'city'   => '%L',
    //                                 'state'  => '%A1',
    //                                 'zip'    => '%z',
    //                             ])
    //                             ->draggable()
    //                             ->clickable(true)
    //                             ->geolocate()
    //                             ->geolocateLabel('Get Location')
    //                         // ->geolocateOnLoad(true, false)
    //                         ,
    //                     ]),
    //             ]),
    //         ]);

    // }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Building Name')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('From')->searchable(),
            ])
            ->headerActions([
                // CreateAction::make()
                //     ->label('Create Building')
                //     ->slideOver()
                //     ->after(function(Set $set){
                //         dd($this->mountedTableActionForm->model);
                //         // dd($this->ownerRecord->building);
                //         $buildingRecord = DB::table('building_owner_association')
                //         ->where('id', $this->id);
                //         dd($buildingRecord);
                //     }),
                // AttachAction::make()
                // ->preloadRecordSelect()
                // ->recordSelectOptionsQuery(function (Builder $query) {
                //     return $query->whereDoesntHave('ownerAssociations');
                // })
                // ->form(fn(AttachAction $action): array=> [
                //     $action->getRecordSelect()->required(),
                //     DatePicker::make('from')->default(Carbon::now()->format('d-M-Y')),
                // ])
                // ->label('Add existing building'),

                Action::make('Attach Building')
                    ->slideOver()
                    ->form([
                        Select::make('building_id')
                            ->label('Building')
                            ->options(function (RelationManager $livewire) {
                                $existingbuildingIds = DB::table('building_owner_association')
                                    ->pluck('building_id');

                                return Building::whereNotIn('id', $existingbuildingIds)->pluck('name');

                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->optionsLimit(500)
                            ->required()
                            ->live()
                            ->createOptionForm([
                                Grid::make(['default' => 2])

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
                                                ->visible(Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager')
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
                                                                    'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;',
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
                                                                    'style' => 'background-color: #f0f0f0; color: #6c757d; pointer-events: none;',
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
                                                        ->afterStateUpdated(function ($state, Set $set) {
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
                                    ]),
                            ]),
                    ]),

                Action::make('feature')
                    ->label('Upload Buildings') // Set a label for your action
                    ->form([
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

                        $filePath = $data['excel_file'];
                        $fullPath = storage_path('app/' . $filePath);
                        $oaId     = $this->ownerRecord->id;

                        if (!file_exists($fullPath)) {
                            Log::error("File not found at path: ", [$fullPath]);
                        }

                        // Now import using the file path
                        Excel::import(new BuildingImport($oaId), $fullPath); // Notify user of success
                    }),
                ExportAction::make('exporttemplate')->exports([
                    ExcelExport::make()
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                        ->withColumns([
                            Column::make('name'),
                            Column::make('property_group_id'),
                            Column::make('address_line1'),
                            Column::make('area'),
                        ]),
                ])->label('Download sample format file'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
