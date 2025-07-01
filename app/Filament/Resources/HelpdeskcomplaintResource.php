<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use Filament\Facades\Filament;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use App\Models\Vendor\ServiceVendor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Vendor\SelectServicesController;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\HelpdeskcomplaintResource\Pages;
use App\Filament\Resources\ComplaintscomplaintResource\RelationManagers\CommentsRelationManager;

class HelpdeskcomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel     = 'Facility Support Issues';

    protected static ?string $navigationGroup = 'Facility Support';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->columns(2)
                    ->schema([
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->disabled()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->required(function (Get $get) {
                                if ($get('category') == 'Security Services') {
                                    return false;
                                }
                                return true;
                            })
                            ->options(function (Complaint $record, Get $get) {
                                $serviceVendor = ServiceVendor::where('service_id', $get('service_id'))->pluck('vendor_id');
                                // if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                //     return Vendor::whereIn('id', $serviceVendor)->whereHas('ownerAssociation', function ($query) {
                                //         $query->where('owner_association_id', Filament::getTenant()->id);
                                //     })->pluck('name', 'id');
                                // }
                                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                    $mainQuery = Vendor::whereHas('ownerAssociation', function ($query) {
                                        $query->where('owner_association_id', Filament::getTenant()->id);
                                    });
                                    $mainQuery =  ($record->category !== 'Other') ? $mainQuery->whereIn('id', $serviceVendor) : $mainQuery;
                                    return $mainQuery->pluck('name', 'id');
                                }
                                return Vendor::whereIn('id', $serviceVendor)->pluck('name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                if ($record->category == 'Security Services') {
                                    return true;
                                }
                                if ($record->vendor_id == null) {
                                    return false;
                                }
                                // return true; //TODO verify when we can disable this field
                            })
                            ->live()
                            ->searchable()
                            ->label('Vendor name'),
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->required()
                            ->disabled()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->preload()
                            ->placeholder('Unit Number'),
                        TextInput::make('ticket_number')->disabled(),
                        Select::make('technician_id')
                            ->relationship('technician', 'first_name')
                            ->options(function (Complaint $record, Get $get) {
                                $technician_vendor = DB::table('service_technician_vendor')->where('service_id', $record->service_id)->pluck('technician_vendor_id');
                                $technicians       = TechnicianVendor::find($technician_vendor)->where('vendor_id', $get('vendor_id'))->pluck('technician_id');
                                return User::find($technicians)->pluck('first_name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->preload()
                            ->searchable()
                            ->label('Technician name'),
                        TextInput::make('priority')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if ($value < 1 || $value > 3) {
                                            $fail('The priority field accepts 1, 2 and 3 only.');
                                        }
                                    };
                                },
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->numeric(),
                        DatePicker::make('due_date')
                            // ->minDate(now()->format('Y-m-d'))
                            ->rules(['date'])
                            ->disabled(function (Complaint $record) {
                                return $record->status != 'open';
                            })
                            ->placeholder('Due Date'),
                        Repeater::make('media')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->label('Image'),
                            ])
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->preload()
                            // ->disabled()
                            ->searchable()
                            ->label('Service'),
                        // TextInput::make('category')->disabled(),
                        Select::make('category')
                            ->required()
                            ->options(fn() => Service::whereIn('id', [5, 36, 69, 40, 228])->pluck('name', 'id')->toArray())
                            ->preload()
                            ->placeholder('Select a service')
                            ->afterStateHydrated(function (Select $component, $state) {
                                if ($state && is_string($state)) {
                                    $service = Service::where('name', $state)->first();
                                    if ($service) {
                                        $component->state($service->id);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state && is_numeric($state)) {
                                    $service = Service::find($state);
                                    if ($service) {

                                        $set('category', $service->name);
                                    }
                                }
                            }),


                        TextInput::make('open_time')->disabled(),
                        TextInput::make('close_time')->disabled()->default('NA'),
                        Textarea::make('complaint')
                            ->disabled()
                            ->placeholder('Complaint'),
                        // Textarea::make('complaint_details')
                        //     ->disabled()
                        //     ->placeholder('Complaint Details'),
                        TextInput::make('type')->label('Type')
                            ->disabled()
                            ->default('NA'),
                        Toggle::make('Urgent')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                // dd($record->priority);
                                if ($record->priority == 1) {
                                    return true;
                                } else {
                                    return false;
                                }
                            })
                            ->default(false)
                            ->hidden(function ($record) {
                                if ($record->type == 'personal') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->disabled(),
                        Section::make('Status and Remarks')
                            ->columns(2)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'open'   => 'Open',
                                        'closed' => 'Closed',
                                    ])
                                    ->disabled(function (Complaint $record) {
                                        return $record->status != 'open';
                                    })
                                    ->searchable()
                                    ->live(),
                                Textarea::make('remarks')
                                    ->rules(['max:250'])
                                    // ->visible(function (callable $get) {
                                    //     if ($get('status') == 'closed') {
                                    //         return true;
                                    //     }
                                    //     return false;
                                    // })
                                    ->disabled(function (Complaint $record) {
                                        return $record->status != 'open';
                                    })
                                    ->required(),
                            ]),

                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->where('complaintable_type', 'App\Models\Building\Building')->withoutGlobalScopes())
            // ->poll('60s')
            ->columns([
                // ViewColumn::make('name')->view('tables.columns.combined-column')
                //     ->toggleable(),
                TextColumn::make('open_time')
                    ->tooltip(fn(Model $record): string => "Complaint:- {$record->complaint}")
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Open At'),
                TextColumn::make('due_date')
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Due Date')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state || $state === 'NA') {
                            return 'NA';
                        }

                        $dueDate = Carbon::parse($state);
                        $today = Carbon::today();

                        // Status check: if status is 'close', just show 'Closed' or actual date
                        if (strtolower($record->status) === 'close') {
                            return 'Closed'; // or: return $dueDate->format('d M Y');
                        }

                        if ($dueDate->isToday()) {
                            return 'Due today';
                        }

                        if ($dueDate->isFuture()) {
                            $daysLeft = $dueDate->diffInDays($today);
                            return $daysLeft === 1 ? '1 day left' : "$daysLeft days left";
                        }

                        // If overdue and not closed
                        $daysOver = $today->diffInDays($dueDate);
                        return $daysOver === 1 ? '1 day over' : "$daysOver days over";
                    }),
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'success' => 'open',
                        'danger'  => 'closed',
                        'primary' => fn($state) => $state === null || $state === 'in-progress',
                    ])
                    ->formatStateUsing(fn($state) => $state === null || $state === 'in-progress' ? 'Pending' : ucfirst($state))
                    ->default('--'),
                TextColumn::make('ticket_number')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Ticket number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('type')
                    ->formatStateUsing(fn(string $state) => Str::ucfirst($state))
                    ->default('NA'),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                // TextColumn::make('complaint')
                //     ->toggleable()
                //     ->limit(20)
                //     ->searchable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload(),
                SelectFilter::make('status')
                        ->options([
                            'open'   => 'Open',
                            'closed' => 'Closed',
                        ])
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
            'index' => Pages\ListHelpdeskcomplaints::route('/'),
            // 'view' => Pages\ViewHelpdeskcomplaint::route('/{record}'),
            'edit'  => Pages\EditHelpdeskcomplaint::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_helpdeskcomplaint');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_helpdeskcomplaint');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_helpdeskcomplaint');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_helpdeskcomplaint');
    }
}
