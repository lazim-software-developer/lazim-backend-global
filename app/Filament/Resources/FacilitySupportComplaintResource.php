<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\FacilitySupportComplaintResource\Pages;
use App\Models\Accounting\SubCategory;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\OwnerAssociation;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Closure;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FacilitySupportComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Reactive Maintenance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Complaint Details')
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 1, 'lg' => 2])
                            ->schema([
                                Select::make('type')
                                    ->label('Complaint Type')
                                    ->options([
                                        'personal' => 'Personal',
                                        'building' => 'Building',
                                    ])
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('flat_id', null);
                                    })
                                    ->disabledOn('edit')
                                    ->default('NA'),

                                Toggle::make('Urgent')
                                    ->label('Mark as Urgent')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('priority', 1);
                                            Notification::make()
                                                ->title('Complaint Marked as Urgent')
                                                ->body('The complaint has been marked as urgent and priority has been set to 1.')
                                                ->icon('heroicon-o-exclamation-triangle')
                                                ->send();
                                        } else {
                                            $set('priority', 3);
                                            Notification::make()
                                                ->title('Complaint Marked as Non-Urgent')
                                                ->body('The complaint has been marked as non-urgent and priority has been set to 3.')
                                                ->icon('heroicon-o-exclamation-circle')
                                                ->send();
                                        }
                                    })
                                    ->onIcon('heroicon-o-exclamation-triangle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->onColor('danger')
                                    ->visible(function (callable $get) {
                                        if ($get('type') == 'personal') {
                                            return true;
                                        }return false;
                                    })
                                    ->default(false),

                                Select::make('building_id')
                                    ->label('Building')
                                    ->options(function () {
                                        $buildingIds = DB::table('building_owner_association')
                                            ->where('owner_association_id', auth()->user()->owner_association_id)
                                            ->where('active', true)
                                            ->pluck('building_id');

                                        return Building::whereIn('id', $buildingIds)
                                            ->pluck('name', 'id');
                                    })
                                    ->reactive()
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->disabledOn('edit')
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('flat_id', null);
                                    })
                                    ->placeholder('Select Building'),

                                Select::make('flat_id')
                                    ->label('Unit Number')
                                    ->options(function (callable $get) {
                                        $pmFlats = DB::table('property_manager_flats')
                                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->where('active', true)
                                            ->pluck('flat_id')
                                            ->toArray();

                                        return Flat::where('building_id', $get('building_id'))
                                            ->whereIn('id', $pmFlats)
                                            ->pluck('property_number', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->visible(function (callable $get) {
                                        if ($get('type') == 'personal') {
                                            return true;
                                        }return false;
                                    })
                                    ->disabledOn('edit')
                                    ->placeholder('Select Unit Number'),

                                TextInput::make('ticket_number')
                                    ->label('Ticket Number')
                                    ->disabledOn('edit')
                                    ->visibleOn('edit'),

                                DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->minDate(now()->format('Y-m-d'))
                                // ->maxDate(now()->addDays(3)->format('Y-m-d'))
                                    ->rules(['date'])
                                    ->validationMessages([
                                        'maxDate' =>
                                        'The due date should be within 3 days of the complaint creation date.',
                                    ])
                                    ->disabledOn('edit')
                                    ->placeholder('Select Due Date'),

                                Textarea::make('complaint')
                                    ->label('Complaint Description')
                                    ->disabledOn('edit')
                                    ->required()
                                    ->placeholder('Describe the complaint in brief'),

                                TextInput::make('priority')
                                    ->label('Priority')
                                    ->reactive()
                                    ->default('3')
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state == 1) {
                                            $set('Urgent', true);

                                        } else {
                                            $set('Urgent', false);

                                        }
                                    })
                                    ->visibleOn('edit')
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, Closure $fail) {
                                                if ($value < 1 || $value > 3) {
                                                    $fail('Priority must be between 1 and 3.');
                                                }
                                            };
                                        },
                                    ])
                                    ->numeric()
                                    ->placeholder('Priority: 1 (High) - 3 (Low)'),
                            ]),

                    ])
                    ->columns(['sm' => 1, 'md' => 2]),

                Section::make('Service & Technician Details')
                    ->collapsible()
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 1, 'lg' => 2])
                            ->schema([

                                Select::make('subcategory_id')
                                    ->options(SubCategory::all()->pluck('name', 'id'))
                                    ->live()
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Select Sub-Category')
                                    ->label('Sub Category')
                                    ->preload()
                                    ->disabledOn('edit')
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('vendor_id', null);
                                        $set('technician_id', null);
                                        $set('service_id', null);
                                    }),

                                Select::make('service_id')
                                    ->label('Service')
                                    ->live()
                                    ->preload()
                                    ->required()
                                    ->options(function (callable $get) {
                                        return Service::where('type', 'vendor_service')
                                            ->where('subcategory_id', $get('subcategory_id'))->pluck('name', 'id');
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $serviceName = Service::where('id', $state)->pluck('name');
                                        $set('category', $serviceName);

                                        $set('vendor_id', null);
                                        $set('technician_id', null);
                                    })
                                    ->searchable()
                                    ->disabledOn('edit')
                                    ->placeholder('Select Service'),

                                Select::make('vendor_id')
                                    ->label('Vendor Name')

                                // ->relationship('vendor', 'name')
                                    ->preload()
                                // ->required(function (Get $get) {
                                //     return $get('category') != 'Security Services';
                                // })
                                    ->searchable()
                                    ->placeholder('Select Vendor')
                                    ->options(function (Get $get) {
                                        $serviceId = $get('service_id');

                                        if (! $serviceId) {
                                            return [];
                                        }
                                        $vendorIds = ServiceVendor::where('service_id', $get('service_id'))
                                            ->pluck('vendor_id');
                                        // dd($vendorIds);

                                        // $vendorIds = DB::table('service_technician_vendor')
                                        //     ->join('technician_vendors', 'service_technician_vendor.technician_vendor_id'
                                        //         , '=', 'technician_vendors.id')
                                        //     ->where('service_technician_vendor.service_id', $serviceId)
                                        //     ->where('service_technician_vendor.active', true)
                                        //     ->where('technician_vendors.active', true)
                                        //     ->pluck('technician_vendors.vendor_id')
                                        //     ->unique()
                                        //     ->toArray();

                                        // return User::whereIn('id', $vendorIds)
                                        //     ->orderBy('first_name')
                                        //     ->pluck('first_name', 'id')
                                        //     ->toArray();

                                        if(Service::find($serviceId)->name == 'Other'){
                                            return Vendor::where('owner_association_id',
                                            auth()->user()?->owner_association_id)
                                            ->where('status', 'approved')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                        }
                                        return Vendor::where('owner_association_id',
                                            auth()->user()?->owner_association_id)
                                            ->where('status', 'approved')
                                            ->whereIn('id', $vendorIds)
                                            ->pluck('name', 'id')
                                            ->toArray();

                                    })
                                    ->searchable()
                                    ->live(),

                                Select::make('technician_id')
                                    ->label('Technician')
                                    ->options(function (Get $get) {
                                        $serviceId = $get('service_id');
                                        $vendorId  = $get('vendor_id');

                                        if (! $serviceId) {
                                            return [];
                                        }

                                        $technicianIds = DB::table('service_technician_vendor')
                                            ->join('technician_vendors', 'service_technician_vendor.technician_vendor_id'
                                                , '=', 'technician_vendors.id')
                                            ->where('service_technician_vendor.service_id', $serviceId)
                                            ->where('technician_vendors.vendor_id', $vendorId)
                                            ->where('service_technician_vendor.active', true)
                                            ->where('technician_vendors.active', true)
                                            ->pluck('technician_vendors.technician_id')
                                            ->unique()
                                            ->toArray();
                                        if(Service::find($serviceId)->name == 'Other'){
                                            $technicianIds = TechnicianVendor::where('vendor_id', $vendorId)
                                            ->pluck('technician_id')
                                            ->toArray();
                                        }
                                        return User::whereIn('id', $technicianIds)
                                            ->orderBy('first_name')
                                            ->pluck('first_name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Assign a Technician')
                                    ->live()
                            ]),

                    ]),

                Section::make('Additional Details')
                    ->visibleOn('edit')
                    ->collapsible()
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 1, 'lg' => 2])
                            ->schema([

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'open'   => 'Open',
                                        'closed' => 'Closed',
                                    ])
                                    ->default('open')
                                    ->visibleOn('edit')
                                    ->searchable()
                                    ->live(),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->rules(['max:250'])
                                    ->required(function (callable $get) {
                                        if ($get('status' === 'closed')) {
                                            return true;
                                        }return false;
                                    })
                                    ->visible(function (callable $get) {
                                        return $get('status') == 'closed';
                                    })
                                    ->placeholder('Add remarks'),

                                DatePicker::make('close_time')
                                    ->displayFormat('d-M-Y')
                                    ->label('Resolved Date')
                                // ->default(now()->format('d-M-Y h:i A'))
                                    ->reactive()
                                    ->required(function (callable $get) {
                                        if ($get('status' === 'closed')) {
                                            return true;
                                        }return false;
                                    })
                                    ->visible(function (callable $get) {
                                        return $get('status') == 'closed';
                                    }),
                            ]),

                        FileUpload::make('media')
                            ->label('Images')
                            ->multiple()
                            ->maxFiles(5)   // Maximum 5 files
                            ->maxSize(2048) // 2MB in kilobytes
                            ->disk('s3')
                            ->directory('dev')
                            ->image()
                            ->enableDownload()
                            ->enableOpen()
                            ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                            ->columnSpanFull()
                            ->downloadable()
                            ->previewable()
                            ->visible(function ($record) {
                                if ($record) {
                                    return $record->media->isNotEmpty();
                                }
                                return false;
                            })
                            ->getUploadedFileNameForStorageUsing(
                                fn($file): string => (string) str()->uuid() . '.' . $file->getClientOriginalExtension()
                            )
                            ->afterStateUpdated(function ($state, $old, $set) {
                                if ($old && ! $state) {
                                    $set('media', null);
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $buildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->where('active', 1)
            ->pluck('building_id');

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        $authOaBuildings = Building::where('owner_association_id', auth()->user()->owner_association_id)
            ->pluck('id');

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($buildingIds, $pmFlats, $authOaBuildings) {
                $baseQuery = $query->whereIn('complaint_type', ['help_desk', 'tenant_complaint']);

                if (auth()->user()->role->name == 'Property Manager'
                || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                 ->pluck('role')[0] == 'Property Manager') {
                    return $baseQuery->whereIn('building_id', $buildingIds)
                        ->where(function ($query) use ($pmFlats) {
                            $query->whereNull('flat_id')
                                ->orWhereIn('flat_id', $pmFlats);
                        })
                        ->latest();
                }

                if (auth()->user()->role->name == 'OA') {
                    return $baseQuery->whereIn('building_id', $buildingIds)
                        ->latest();
                }

                if (auth()->user()->role->name == 'Admin') {
                    return $baseQuery->latest();
                }

                // For all other roles
                return $baseQuery->whereIn('building_id', $authOaBuildings)
                    ->latest();
            })
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket Number')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('building.name')
                    ->label('Building')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state) => ucfirst($state))
                    ->default('NA'),

                TextColumn::make('user.first_name')
                    ->label('User')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),

                TextColumn::make('category')
                    ->label('Category')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),

                TextColumn::make('complaint')
                    ->label('Remarks')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'open'   => 'Open',
                            'closed' => 'Closed',
                        };
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'open'                            => 'primary',
                        'closed'                          => 'gray',
                    })
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (in_array(auth()->user()->role->name, ['Property Manager', 'OA'])) {
                            $buildingIds = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');

                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');
                        }

                        $oaId = auth()->user()?->owner_association_id;
                        return Building::where('owner_association_id', $oaId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('No Issues')
            ->bulkActions([
                // ExportBulkAction::make(),
            ])
            ->actions([]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFacilitySupportComplaints::route('/'),
            'create' => Pages\CreateFacilitySupportComplaint::route('/create'),
            'edit'   => Pages\EditFacilitySupportComplaint::route('/{record}/edit'),
        ];
    }
}
