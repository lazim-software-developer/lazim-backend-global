<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\BuildingResource\RelationManagers\ContractsRelationManager;
use App\Filament\Resources\BuildingResource\RelationManagers\VendorRelationManager;
use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers\AppartmentsafetyRelationManager;
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
use App\Models\Building\BuildingPoc;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use DB;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Unique;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

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
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                    // ->disabled(function () {
                    //     if (auth()->user()->role->name !== 'Admin') {
                    //         return true;
                    //     }
                    // })
                        ->unique('buildings', 'name', fn(?Model $record) => $record)
                        ->placeholder('Name'),

                    Select::make('building_type')
                        ->options([
                            'commercial'  => 'Commercial',
                            'residential' => 'Residential',
                            'residential/commercial' => 'Residential+Commercial',
                        ])
                        ->hidden(fn() => !in_array(auth()->user()->role->name, ['Admin', 'Property Manager'])),

                    TextInput::make('property_group_id')
                        ->rules(['max:50'])
                        ->required()
                        ->placeholder('Property Group Id'),

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
                        ->rules(['max:100'])
                        ->required()
                        ->placeholder('Area'),

                    // Select::make('city_id')
                    //     ->rules(['exists:cities,id'])
                    //     ->preload()
                    //     ->relationship('cities', 'name')
                    //     ->searchable()
                    //     ->placeholder('NA'),
                    MarkdownEditor::make('description')
                        ->columnSpanFull()
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
                        ->columnSpanFull()
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
                        ->rule('regex:/^[0-9\-.,\/_ ]+$/')
                        ->placeholder('Floors')
                        ->disabled(function($record){
                            if($record){
                                return $record->floors!=null;
                            }return false;
                        })
                        ->label('Floor'),

                    TextInput::make('parking_count')
                        ->rule('regex:/^[0-9\-.,\/_ ]+$/')
                        ->live(onBlur: true) // Only trigger on blur (when user leaves the field)
                        ->afterStateUpdated(function($state, $record, Set $set) {
                            if (!$state || !$record) {
                                return;
                            }

                            $flatsParking = Flat::where('building_id', $record->id)
                                ->pluck('parking_count')
                                ->toArray();

                            $totalFlatParking = array_sum(array_filter($flatsParking, fn($value) => !is_null($value)));

                            if ((int)$state < $totalFlatParking) {
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
                                    'zoomControl' => false,
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
                            'commercial'  => 'Commercial',
                            'residential' => 'Residential',
                            'residential/commercial' => 'Residential+Commercial',
                            default       => '--',
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
                            This will remove your management authority and deactivate related flat tenants.'
                        : 'Are you sure you want to attach to this building?
                             This will grant you management authority.';
                    })
                    ->form(function ($record) {
                        $active = DB::table('building_owner_association')
                            ->where('building_id', $record->id)
                            ->where('active', 1)
                            ->exists();

                        if (!$active) {
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

                        if (!$active) {
                            // Check if the building is already associated with another active owner association
                            $isAssociated = DB::table('building_owner_association')
                                ->where('building_id', $record->id)
                                ->where('active', 1)
                                ->exists();

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

                            // Make related flat tenants active
                            DB::table('flat_tenants')
                                ->whereIn('flat_id', function ($query) use ($record) {
                                    $query->select('id')
                                        ->from('flats')
                                        ->where('building_id', $record->id);
                                })
                                ->update(['active' => 1]);

                            // Make related security guards active
                            BuildingPoc::where('building_id', $record->id)
                                ->update(['active' => 1]);

                            // Make related vendor building active
                            $vendorAll = DB::table('owner_association_vendor')
                                ->where(['owner_association_id'=> auth()->user()->owner_association_id, 'active'=> 1])
                                ->pluck('vendor_id');
                            DB::table('building_vendor')
                                ->where('building_id', $record->id)
                                ->whereIn('vendor_id',$vendorAll)
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

                        // Make related flat tenants inactive
                        $flatResidents = DB::table('flat_tenants')
                            ->whereIn('flat_id', function ($query) use ($record) {
                                $query->select('id')
                                    ->from('flats')
                                    ->where('building_id', $record->id);
                            });
                        if($flatResidents->exists()) {
                            $tenantIds = $flatResidents->pluck('tenant_id');
                            foreach ($tenantIds as $tenantId) {
                                $otherFlats = DB::table('flat_tenants')
                                    ->where('tenant_id', $tenantId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                if (!$otherFlats) {
                                    DB::table('refresh_tokens')
                                        ->where('user_id', $tenantId)
                                        ->delete();
                                    User::findOrFail($tenantId)->tokens()->delete();
                                }
                            }
                        }
                        $flatResidents->update(['active' => 0]);
                        // Make related vendor building inactive
                        $vendorAssociated = DB::table('building_vendor')
                            ->where('building_id', $record->id);
                        if($vendorAssociated->exists()) {
                            $vendorIds = $vendorAssociated->pluck('vendor_id');
                            foreach ($vendorIds as $vendorId) {
                                $otherBuildings = DB::table('building_vendor')
                                    ->where('vendor_id', $vendorId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                $vendor = Vendor::findOrFail($vendorId);
                                if (!$otherBuildings) {
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

                                    $user = User::findOrFail($technicianId);
                                    $vendors = $user->technicianVendors()
                                        ->with(['vendor.buildings' => function ($query) {
                                            $query->wherePivot('active', 1);
                                        }])
                                        ->get();
                                    $buildings = $vendors->flatMap(function ($technicianVendor) {
                                        return $technicianVendor->vendor->buildings;
                                    })->unique('id');

                                    if (!$otherBuildings && $buildings->isEmpty()) {
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
                        if($security->exists()) {
                            $userIds = $security->pluck('user_id');
                            foreach ($userIds as $userId) {
                                $otherBuildings = BuildingPoc::where('user_id', $userId)
                                    ->where('building_id', '!=', $record->id)
                                    ->where('active', 1)
                                    ->exists();

                                if (!$otherBuildings) {
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
                    ->label('Upload Budget') // Set a label for your action
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
                    })
                    ->hidden(auth()->user()->role?->name === 'Property Manager'),
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
        if (!$user || !$user->role || $user->role->name !== 'Property Manager') {
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
