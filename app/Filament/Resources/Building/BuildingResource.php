<?php
namespace App\Filament\Resources\Building;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\City;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use App\Models\Vendor\Vendor;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use App\Imports\OAM\BudgetImport;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\Building\BuildingPoc;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Imports\BuildingImport;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\BuildingResource\Pages;
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

    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup       = 'Property Management';
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
                        ->unique('buildings', 'name', fn(?Model $record) => $record)
                        ->placeholder('Name'),
                        Select::make('building_type')
                        ->options([
                            'commercial'             => 'Commercial',
                            'residential'            => 'Residential',
                            'residential/commercial' => 'Residential+Commercial',
                        ])
                        ->hidden(fn() => ! in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])),
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
                        ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                        ->directory('dev')
                        ->image()
                        ->maxSize(2048)
                        ->label('Cover Photo')
                        ->columnSpanFull(),
                        TextInput::make('parking_count')
                        ->rule('regex:/^[0-9\-.,\/_ ]+$/')
                        ->live(onBlur: true) // Only trigger on blur (when user leaves the field)
                        ->afterStateUpdated(function ($state, $record, Set $set) {
                            if (! $state || ! $record) {
                                return;
                            }

                            $flatsParking = Flat::where('building_id', $record->id)
                                ->pluck('parking_count')
                                ->toArray();

                            $totalFlatParking = array_sum(array_filter($flatsParking, fn($value) => ! is_null($value)));

                            if ((int) $state < $totalFlatParking) {
                                Notification::make()
                                    ->title('Invalid parking count')
                                    ->body("Total parking count cannot be less than sum of flat parking counts
                                    ($totalFlatParking)")
                                    ->danger()
                                    ->send();

                                $set('parking_count', null);
                            }
                        })
                        ->placeholder('Total Parking Count')
                        ->label('Total Parking Count'),

                    Toggle::make('allow_postupload')
                        ->rules(['boolean'])
                        ->label('Allow post-upload'),
                    Toggle::make('show_inhouse_services')
                        ->rules(['boolean'])
                        ->label('Show Personal services'),
                    Toggle::make('status')
                        ->rules(['boolean'])
                        ->default(true) // Sets the default value to true (active)
                        ->label('Status'),
                        // ->hiddenOn('create'),

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
                        ->visible(in_array(auth()->user()->role->name, ['Property Manager', 'Admin']))
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
                                    'zoomControl'       => false,
                                    'mapTypeId'         => 'roadmap', // Use this instead of defaultMapType
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

                    Section::make('Contract Details')
                        ->visible(auth()->user()->role->name === 'Property Manager')
                        ->schema([
                            Grid::make(2)->schema([
                                DatePicker::make('from')
                                    ->required()
                                    ->label('Contract Start Date')
                                    ->default(Carbon::now()->format('Y-m-d'))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('to', null);
                                    }),

                                DatePicker::make('to')
                                    ->after('from')
                                    ->label('Contract End Date')
                                    ->required()
                                // ->disabledOn('edit')
                                    ->validationMessages([
                                        'after' => 'The "Contract start date" must be after the "Contract end date".',
                                    ]),
                            ]),
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
                Tables\Columns\TextColumn::make('building_type')
                    ->label('Type')
                    ->default('--')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'commercial'             => 'Commercial',
                            'residential'            => 'Residential',
                            'residential/commercial' => 'Residential+Commercial',
                            default                  => '--',
                        };
                    }),
                Tables\Columns\TextColumn::make('property_group_id')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('address_line1')
                    ->toggleable()
                    ->label('Address')
                    ->searchable()
                    ->default('NA')
                    ->limit(25),

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
                Tables\Actions\DetachAction::make()
                    ->label(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        return $active ? 'Detach' : 'Attach';
                    })
                    ->icon(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        return $active ? 'heroicon-o-x-mark' : 'heroicon-o-plus';
                    })
                    ->visible(function () {
                        return auth()->user()?->role?->name === 'Property Manager';
                    })
                    ->modalHeading(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        return ($active ? 'Detach from ' : 'Attach to ') . $record->name;
                    })
                    ->modalDescription(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        return $active
                        ? 'Are you sure you want to detach from this building?
                            This will remove your management authority and deactivate related flats and flat tenants.'
                        : 'Are you sure you want to attach to this building?
                             This will grant you management authority.';
                    })
                    ->form(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        if (! $active) {
                            return [
                                DatePicker::make('from')
                                    ->label('Contract Start Date')
                                    ->required(),
                                DatePicker::make('to')
                                    ->label('Contract End Date')
                                    ->validationMessages([
                                        'after' => 'The "Contract End date" must be after the "Contract Start date".',
                                    ])
                                    ->required(),
                            ];
                        }

                        return [];
                    })
                    ->modalSubmitActionLabel(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        return $active ? 'Yes, detach' : 'Yes, attach';
                    })
                    ->action(function ($record, array $data) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        if (! $active) {
                            // Check if the building is already associated with another active owner association
                            $isAssociated = DB::table('building_owner_association')
                                ->where('building_id', $record->id)
                                ->where('active', 1)
                                ->exists();

                            $pmId = DB::table('building_owner_association')
                                ->where('building_id', $record->id)
                            // ->where('active', 1)
                                ->pluck('owner_association_id')[0];
                            $pmRole = OwnerAssociation::where('id', $pmId)->first()->role == 'Property Manager';

                            if ($pmRole) {
                                $pmFlats = DB::table('property_manager_flats')
                                    ->where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->whereIn('flat_id', function ($query) use ($record) {
                                        $query->select('id')
                                            ->from('flats')
                                            ->where('building_id', $record->id);
                                    })
                                    ->where('active', 0);

                                $pmFlats->update(['active' => 1]);
                            }
                            if ($isAssociated) {
                                Notification::make()
                                    ->title('Building is already associated with another property manager')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Update the 'from' and 'to' dates and make the record active
                            DB::table('building_owner_association')
                                ->where('building_id', $record->id)
                                ->update([
                                    'from'   => $data['from'],
                                    'to'     => $data['to'],
                                    'active' => 1,
                                ]);

                            $pmFlats = DB::table('property_manager_flats')
                                ->where('owner_association_id', auth()->user()?->owner_association_id)
                                ->whereIn('flat_id', function ($query) use ($record) {
                                    $query->select('id')
                                        ->from('flats')
                                        ->where('building_id', $record->id);
                                })
                                ->where('active', 0);

                            // Make related flat tenants active
                            DB::table('flat_tenants')
                                ->whereIn('flat_id', $pmFlats->pluck('flat_id'))
                                ->update(['active' => 1]);
                            $pmFlats->update(['active' => 1]);

                            // Make related security guards active
                            BuildingPoc::where('building_id', $record->id)
                                ->update(['active' => 1]);

                            // Make related vendor building active
                            $vendorAll = DB::table('owner_association_vendor')
                                ->where(['owner_association_id' => auth()->user()->owner_association_id, 'active' => 1])
                                ->pluck('vendor_id');
                            DB::table('building_vendor')
                                ->where('building_id', $record->id)
                                ->whereIn('vendor_id', $vendorAll)
                                ->update(['active' => 1, 'start_date' => $data['from'], 'end_date' => $data['to']]);

                            Notification::make()
                                ->title('Building attached successfully')
                                ->success()
                                ->send();
                            return;
                        }

                        DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->update(['active' => 0]);

                        // Set the 'to' date to now in 'yyyy-mm-dd' format
                        DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->update(['to' => Carbon::now()->format('Y-m-d')]);

                        $pmFlats = DB::table('property_manager_flats')
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->whereIn('flat_id', function ($query) use ($record) {
                                $query->select('id')
                                    ->from('flats')
                                    ->where('building_id', $record->id);
                            })
                            ->where('active', 1);

                        // Make related flat tenants inactive
                        $flatResidents = DB::table('flat_tenants')
                            ->whereIn('flat_id', $pmFlats->pluck('flat_id'));
                        if ($flatResidents->exists()) {
                            $tenantIds = $flatResidents->pluck('tenant_id');
                            foreach ($tenantIds as $tenantId) {
                                $otherFlats = DB::table('flat_tenants')
                                    ->where('tenant_id', $tenantId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                if (! $otherFlats) {
                                    DB::table('refresh_tokens')
                                        ->where('user_id', $tenantId)
                                        ->delete();
                                    User::findOrFail($tenantId)->tokens()->delete();
                                }
                            }
                        }
                        $flatResidents->update(['active' => 0]);
                        $pmFlats->update(['active' => 0]);
                        // Make related vendor building inactive
                        $vendorAssociated = DB::table('building_vendor')
                            ->where('building_id', $record->id);
                        if ($vendorAssociated->exists()) {
                            $vendorIds = $vendorAssociated->pluck('vendor_id');
                            foreach ($vendorIds as $vendorId) {
                                $otherBuildings = DB::table('building_vendor')
                                    ->where('vendor_id', $vendorId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                $vendor = Vendor::findOrFail($vendorId);
                                if (! $otherBuildings) {
                                    $userId = $vendor->owner_id;
                                    DB::table('refresh_tokens')
                                        ->where('user_id', $userId)
                                        ->delete();
                                    User::findOrFail($userId)->tokens()->delete();
                                }
                                $technicianIds = DB::table('technician_vendors')
                                    ->where('vendor_id', $vendorId)
                                    ->pluck('technician_id');
                                foreach ($technicianIds as $technicianId) {
                                    $otherBuildings = DB::table('building_vendor')
                                        ->where('vendor_id', $vendorId)
                                        ->where('building_id', '!=', $record->id)
                                        ->where('active', 1)
                                        ->exists();

                                    $user    = User::findOrFail($technicianId);
                                    $vendors = $user->technicianVendors()
                                        ->with(['vendor.buildings' => function ($query) {
                                            $query->wherePivot('active', 1);
                                        }])
                                        ->get();
                                    $buildings = $vendors->flatMap(function ($technicianVendor) {
                                        return $technicianVendor->vendor->buildings;
                                    })->unique('id');

                                    if (! $otherBuildings && $buildings->isEmpty()) {
                                        DB::table('refresh_tokens')
                                            ->where('user_id', $technicianId)
                                            ->delete();
                                        $user->tokens()->delete();
                                    }
                                }

                            }
                        }
                        $vendorAssociated->update(['active' => 0, 'end_date' => Carbon::now()->format('Y-m-d')]);
                        // Make related security guards inactive
                        $security = BuildingPoc::where('building_id', $record->id);
                        if ($security->exists()) {
                            $userIds = $security->pluck('user_id');
                            foreach ($userIds as $userId) {
                                $otherBuildings = BuildingPoc::where('user_id', $userId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                if (! $otherBuildings) {
                                    DB::table('refresh_tokens')
                                        ->where('user_id', $userId)
                                        ->delete();
                                    User::findOrFail($userId)->tokens()->delete();
                                }
                            }
                        }
                        $security->update(['active' => 0]);
                        Notification::make()
                            ->title($active ? 'Building detached successfully' : 'Building attached successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('feature')
                    ->label('Upload Budget')                   // Set a label for your action
                    ->modalHeading('Upload Budget for Period') // Modal headin
                    ->form([
                        Forms\Components\Select::make('budget_period')
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
                            ->required(),
                        Forms\Components\FileUpload::make('excel_file')
                            ->label('Upload File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                                'application/vnd.ms-excel',                                          // for .xls
                            ])
                            ->required()
                            ->disk('local')                // or your preferred disk
                            ->directory('budget_imports'), // or your preferred directory
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        // try {
                        $budgetPeriod = $data['budget_period'];
                        $filePath     = $data['excel_file'];
                        $fullPath     = storage_path('app/' . $filePath);

                        if (! file_exists($fullPath)) {
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
                    })
                    ->hidden(auth()->user()->role?->name === 'Property Manager'),
                    Action::make('delete')
                    ->button()
                    ->visible(function () {
                        $auth_user = auth()->user();
                        $role      = Role::where('id', $auth_user->role_id)->first()?->name;
    
                        if ($role === 'Admin' || $role === 'Property Manager') {
                            return true;
                        }
                    })
                    ->action(function ($record,) {
                        if(!empty(auth()->user()?->owner_association_id)) {
                            DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->delete(); 
                        }else{
                            DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->delete(); 
                        }
                        if(!empty(auth()->user()?->owner_association_id)) {
                            DB::table('floors')
                            ->where('building_id', $record->id)
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->delete(); 
                        }else{
                            DB::table('floors')
                            ->where('building_id', $record->id)
                            ->delete(); 
                        }
                        // Then, soft delete the corresponding user in the secondary database
                        $secondaryConnection = DB::connection(env('SECOND_DB_CONNECTION'));
                        $secondaryConnection->table('users')
                        ->where('building_id', $record->id)
                        ->update(['deleted_at' => now()]);
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
                            ->formatStateUsing(fn ($record) => 
                                $record->CreatedBy->first_name.' '.$record->CreatedBy->last_name ?? 'N/A'
                            ), 
                            // Custom column using relationship
                            Column::make('owner_association_id')
                            ->heading('Owner Association Name')
                            ->formatStateUsing(fn ($record) => 
                                $record->ownerAssociationData->name ?? 'N/A'
                            ), 
                            Column::make('name')
                                ->heading('Building Name'),
                            Column::make('building_type')
                                ->heading('Building Type'),
                            Column::make('floors')
                                ->heading('Floors'),
                            Column::make('property_group_id')
                                ->heading('Property Group ID')
                                ->formatStateUsing(fn ($record) => 
                                    $record->property_group_id ?? 'N/A'
                                ),
                            Column::make('address_line1')
                                ->heading('Address Line 1')
                                ->formatStateUsing(fn ($record) => 
                                    $record->address_line1 ?? 'N/A'
                                ),
                            Column::make('address_line2')
                            ->heading('Address Line 2')
                            ->formatStateUsing(fn ($record) => 
                                $record->address_line2 ?? 'N/A'
                            ),
                            Column::make('area')
                                ->heading('Area')
                                ->formatStateUsing(fn ($record) => 
                                    $record->area ?? 'N/A'
                                ),
                            Column::make('city_id')
                            ->heading('City')
                            ->formatStateUsing(fn ($record) => 
                                $record->cities->name ?? 'N/A'
                            ),    
                            // Formatted date with custom accessor
                            Column::make('created_at')
                                ->heading('Created Date')
                                ->formatStateUsing(fn ($state) => 
                                    $state ? $state->format('d/m/Y') : ''
                                ),
                                Column::make('status')
                                ->heading('Status')
                                ->formatStateUsing(fn ($record) => 
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
            // ->headerActions([
            //     Action::make('import')
            //             ->visible(function () {
            //                 $auth_user = auth()->user();
            //                 $role      = Role::where('id', $auth_user->role_id)->first()?->name;

            //                 if ($role === 'Admin' || $role === 'Property Manager') {
            //                     return true;
            //                 }
            //             })
            //         ->label('Import Buildings')
            //         ->form([
            //             Section::make()
            //                 ->schema([
            //                     View::make('components.sample-file-download')
            //                         ->view('components.sample-file-download'),
            //                     FileUpload::make('file')
            //                         ->label('Choose CSV File')
            //                         ->disk('local')
            //                         ->directory('temp-imports')
            //                         ->acceptedFileTypes([
            //                             'text/csv',
            //                             'text/plain',
            //                             'application/csv',
            //                         ])
            //                         ->maxSize(5120)
            //                         ->required()
            //                         ->helperText('Upload your CSV file in the correct format')
            //                 ])
            //         ])
            //         ->action(function (array $data) {
            //             try {
            //                 $import = new BuildingImport();
            //                 Excel::import($import, $data['file']);
                            
            //                 $result = $import->getResultSummary();
                            
            //                 if($result['status']===200)
            //                 {
            //                 // Generate detailed report
            //                 $report = "Import Report " . now()->format('Y-m-d H:i:s') . "\n\n";
            //                 $report .= "Successfully imported: {$result['imported']}\n";
            //                 $report .= "Skipped (already exists): {$result['skip']}\n";
            //                 $report .= "Errors: {$result['error']}\n\n";
                            
            //                 // Add detailed error and skip information
            //                 foreach ($result['details'] as $detail) {
            //                     $report .= "Row {$detail['row_number']}: {$detail['message']}\n";
            //                     $report .= "Data: " . json_encode($detail['data']) . "\n\n";
            //                 }
                            
            //                 // Save report
            //                 $filename = 'building-import-' . now()->format('Y-m-d-H-i-s') . '.txt';
            //                 $reportPath = 'import-reports/' . $filename;
            //                 Storage::disk('local')->put($reportPath, $report);
            //                 }
            //                 if($result['status']===401)
            //                 {
            //                     Notification::make()
            //                     ->title('invalid File')
            //                     ->body("{$result['error']}")
            //                     ->danger()
            //                     ->persistent()
            //                     ->send();
            //                 }else{
            //                 // Show notification with results
            //                 Notification::make()
            //                     ->title('Import Complete')
            //                     ->body(
            //                         collect([
            //                             "Successfully imported: {$result['imported']}",
            //                             "Skipped: {$result['skip']}",
            //                             "Errors: {$result['error']}"
            //                         ])->join("\n")
            //                     )
            //                     ->actions([
            //                         \Filament\Notifications\Actions\Action::make('download_report')
            //                         ->label('Download Report')
            //                         ->url(route('download.import.report', ['filename' => $filename]))
            //                         ->openUrlInNewTab()
            //                     ])
            //                     ->success()
            //                     ->persistent()
            //                     ->send();
            //                 }

            //             } catch (\Exception $e) {
            //                 Notification::make()
            //                     ->title('Import Failed')
            //                     ->body($e->getMessage())
            //                     ->danger()
            //                     ->send();
            //             }

            //             // Clean up temporary file
            //             Storage::disk('local')->delete($data['file']);
            //         })
            // ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        $user = Auth::user();

        // All available relations
        $allRelations = [
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            // BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            FloorsRelationManager::class,
            RuleregulationsRelationManager::class,
            // AppartmentsafetyRelationManager::class,
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

        // If user is not logged in, has no role, or is not a Property Manager, show all relations
        if (! $user || ! $user->role || $user->role->name !== 'Property Manager') {
            return $allRelations;
        }

        // Relations specifically for Property Manager
        $propertyManagerRelations = [
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            // BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            FloorsRelationManager::class,
            RuleregulationsRelationManager::class,
            // AppartmentsafetyRelationManager::class,
            EmergencyNumbersRelationManager::class,
            BuildingserviceRelationManager::class,
            // BuildingResource\RelationManagers\ComplaintRelationManager::class,
            // IncidentsRelationManager::class,
            BuildingResource\RelationManagers\ServiceRelationManager::class,
            // BuildingResource\RelationManagers\DocumentsRelationManager::class,
            BuildingResource\RelationManagers\FacilitiesRelationManager::class,
            // BuildingResource\RelationManagers\FlatsRelationManager::class,
            // BuildingResource\RelationManagers\VendorRelationManager::class,
            // BuildingvendorRelationManager::class,
            // VendorsRelationManager::class,
            BuildingResource\RelationManagers\AssetsRelationManager::class,
            ContractsRelationManager::class,
            // VendorRelationManager::class,
        ];

        return $propertyManagerRelations;
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
