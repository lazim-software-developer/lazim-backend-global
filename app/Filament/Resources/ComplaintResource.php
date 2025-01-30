<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Accounting\SubCategory;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ComplaintResource extends Resource
{
    protected static ?string $model      = Complaint::class;
    protected static ?string $modelLabel = 'Preventive Maintenance Schedule';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Schedule')
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 1, 'lg' => 2])
                            ->schema([
                                Hidden::make('type')
                                    ->default('building'),

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
                                    ->placeholder('Select Building'),

                                TextInput::make('ticket_number')
                                    ->label('Ticket Number')
                                    ->disabledOn('edit')
                                    ->visibleOn('edit'),

                                DatePicker::make('due_date')
                                    ->label('Date')
                                    ->minDate(now()->format('Y-m-d'))
                                // ->maxDate(now()->addDays(3)->format('Y-m-d'))
                                    ->rules(['date'])
                                    ->disabledOn('edit')
                                    ->placeholder('Select Date'),

                                Textarea::make('complaint')
                                    ->label('Schedule Details')
                                    ->disabledOn('edit')
                                    ->required()
                                    ->placeholder('Describe the Schedule Details in brief'),
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
                                    ->label('Facility Manager')

                                // ->relationship('vendor', 'name')
                                    ->preload()
                                // ->required(function (Get $get) {
                                //     return $get('category') != 'Security Services';
                                // })
                                    ->searchable()
                                    ->placeholder('Select Facility Manager')
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

                                        return Vendor::whereIn('id', $vendorIds)
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
                    ->collapsible()
                    ->visibleOn('edit')
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 1, 'lg' => 2])
                            ->schema([

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'open'   => 'Open',
                                        'closed' => 'Completed',
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
                                    ->placeholder('Add Remarks'),

                                DatePicker::make('close_time')
                                    ->displayFormat('d-M-Y')
                                    ->label('Date')
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

                        // Repeater::make('photo')
                        //     ->label('Attachments')
                        //     ->schema([
                        //         FileUpload::make('photo')
                        //             ->label('File')
                        //             ->disk('s3')
                        //             ->directory('dev')
                        //             ->image()
                        //             ->maxSize(2048)
                        //             ->openable(true)
                        //             ->downloadable(true),
                        //     ]),

                        FileUpload::make('media')
                            ->label('Images')
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(2048)
                            ->disk('s3')
                            ->directory('dev')
                            ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                            ->image()
                            ->enableDownload()
                            ->visible(function ($record) {
                                if ($record) {
                                    return $record->media->isNotEmpty();
                                }
                                return false;
                            })
                            ->enableOpen()
                            ->columnSpanFull()
                            ->downloadable()
                            ->previewable()
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

        $authOaBuildings = Building::where('owner_association_id', auth()->user()->owner_association_id)
            ->pluck('id');

        $role = auth()->user()->role->name;
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($buildingIds, $role, $authOaBuildings) {
                if (in_array($role, ['OA', 'Property Manager'])) {
                    return $query->whereIn('building_id', $buildingIds)
                        ->where('complaint_type', 'preventive_maintenance')
                        ->latest();
                }
                if ($role == 'Admin') {
                    return $query->where('complaint_type', 'preventive_maintenance')
                        ->latest();
                }
                return $query
                    ->where('complaint_type', 'preventive_maintenance')
                    ->whereIn('building_id', $authOaBuildings);
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'open'                                       => 'Open',
                        'closed'                                     => 'Closed',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'open'                            => 'primary',
                        'closed'                          => 'gray',
                    })
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
            ])
            ->emptyStateHeading('No Preventive Maintenance Schedules')
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
            ->emptyStateHeading('No Preventive Maintenance Schedules')
            ->bulkActions([
                // ExportBulkAction::make(),
            ])
            ->actions([]);
    }

    public static function getRelations(): array
    {
        return [
            // CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit'   => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
