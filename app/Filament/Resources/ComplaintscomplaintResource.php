<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Tables;
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
use Illuminate\Support\Facades\Log;
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
use PhpParser\Node\Stmt\Label;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ComplaintscomplaintResource\Pages;
use App\Filament\Resources\ComplaintscomplaintResource\RelationManagers\CommentsRelationManager;

class ComplaintscomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel     = 'Happiness Center Complaints';

    protected static ?string $navigationGroup = 'Happiness center';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Complaint Details')->schema([
                    Grid::make([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ])->schema([
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
                            ->required()
                            ->options(function (Complaint $record, Get $get) {

                                $serviceVendor = ServiceVendor::where('service_id', $get('service_id'))->pluck('vendor_id');
                                // if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                //     return Vendor::whereIn('id', $serviceVendor)->whereHas('ownerAssociation', function ($query) {
                                //         $query->where('owner_association_id', Filament::getTenant()->id);
                                //     })->pluck('name', 'id');
                                // }
                                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                    $mainQuery = Vendor::whereHas('ownerAssociation', function ($query) use ($record) {

                                        $query->where('owner_association_id', $record->owner_association_id);
                                    });
                                    $mainQuery =  ($record->category !== 'Other') ? $mainQuery->whereIn('id', $serviceVendor) : $mainQuery;
                                    return $mainQuery->pluck('name', 'id');
                                }
                                return Vendor::whereIn('id', $serviceVendor)->pluck('name', 'id');
                            })
                            ->disabled(function (Complaint $record) {
                                if ($record->vendor_id == null) {
                                    return false;
                                }
                                // return true; //TODO verify when we can disable this field
                            })
                            ->live()
                            ->searchable()
                            ->label('Vendor'),
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            // ->required()
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
                                return $record->status == 'closed';
                            })
                            ->preload()
                            ->searchable()
                            ->label('Technician'),
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
                                return $record->status == 'closed';
                            })
                            ->numeric(),
                        DatePicker::make('due_date')
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->rules(['date'])
                            ->placeholder('Due Date'),

                        // TextInput::make('category'),

                        // Select::make('category')
                        //     ->required()
                        //     ->options(function (Complaint $record) {
                        //         return Service::whereIn('id', [5, 36, 69, 40, 228])->pluck('name', 'id');
                        //     })
                        //     ->searchable()
                        //     ->preload()
                        //     ->placeholder('Service'),
                        // Select::make('service_id')
                        //     ->relationship('service', 'name')
                        //     ->preload()
                        //     // ->disabled()
                        //     ->searchable()
                        //     ->label('Service'),
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
                        // Map ID => Name for dropdown options
                        // ->reactive(),
                        // ->disabled(),
                        TextInput::make('open_time')->disabled(),
                        TextInput::make('close_time')->disabled()->default('NA'),
                        Textarea::make('complaint')
                            ->disabled()
                            ->placeholder('Complaint'),
                        TextInput::make('type')->label('Type')
                            ->disabled()
                            ->default('NA'),
                        Toggle::make('Urgent')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
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
                    ]),
                ]),
                Section::make('Media Attachments')->schema([
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
                                ->label('File'),
                        ])
                        ->columns(2),
                ]),
                Section::make('Status and Remarks')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'open'        => 'Open',
                                'in-progress' => 'In-Progress',
                                'closed'      => 'Closed',
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->searchable()
                            ->live(),
                        Textarea::make('remarks')
                            ->rules(['max:250'])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('open_time')
                    ->tooltip(fn(Model $record): string => "Complaint:- {$record->opem_time} \n Ticket Number:- {$record->ticket_number}")
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->limit(10)
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
                    ->sortable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('flat.property_number'),
                TextColumn::make('category'),
                // TextColumn::make('type')
                //     ->formatStateUsing(fn(string $state) => Str::ucfirst($state))
                //     ->default('NA'),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->sortable()
                    ->default('NA')
                    ->searchable()
                    ->tooltip(fn(Model $record): string => $record->user?->first_name ?? 'No name available')
                    ->limit(50),
                TextColumn::make('complaint_type')
                    ->label('Complaint Type')
                    ->formatStateUsing(function($state, $record) {

                        switch ($state) {
                            case 'help_desk':
                                return 'Issues';
                            case 'oa_complaint_report':
                                return 'OA Complaint';
                            case 'preventive_maintenance':
                                return 'Preventive Maintenance';
                            case 'tenant_complaint':
                                return 'Happiness Center';
                            case 'snag':
                                return 'Security Snag';
                            default:
                                return 'NA';
                        }
                    })
                    ->default('NA')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20),
                // TextColumn::make('complaint')
                //     ->toggleable()
                //     ->default('NA')
                //     ->limit(20)
                //     ->searchable()
                //     ->label('Complaint'),
                // TextColumn::make('complaint_details')
                //     ->toggleable()
                //     ->default('NA')
                //     ->limit(20)
                //     ->searchable()
                //     ->label('Complaint Details'),

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
                        'open'        => 'Open',
                        'in-progress' => 'In-Progress',
                        'closed'      => 'Closed',
                    ]),
                SelectFilter::make('complaint_type')
                    ->multiple()
                    ->options([
                        'help_desk'             => 'Issues',
                        'oa_complaint_report'   => 'OA Complaint',
                        'preventive_maintenance' => 'Preventive Maintenance',
                        'tenant_complaint'      => 'Happiness Center',
                        'snag'                  => 'Security Snag',
                    ]),

            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ])
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
            'index' => Pages\ListComplaintscomplaints::route('/'),
            // 'view' => Pages\ViewComplaintscomplaints::route('/{record}'),
            'edit'  => Pages\EditComplaintscomplaint::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_complaintscomplaint');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_complaintscomplaint');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_complaintscomplaint');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_complaintscomplaint');
    }
}
