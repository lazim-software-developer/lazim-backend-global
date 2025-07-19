<?php
namespace App\Filament\Resources;

use DB;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\RentalDetail;
use App\Models\UserApproval;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
// use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\UserApprovalResource\Pages;
use App\Filament\Resources\UserApprovalResource\RelationManagers\HistoryRelationManager;

class UserApprovalResource extends Resource
{
    protected static ?string $model = UserApproval::class;
    protected static ?string $modelLabel = 'Resident Approval';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        $isPropertyManager = auth()->user()?->role->name === 'Property Manager';
        $user = User::find(auth()->user()->id);
        return $form
            ->schema([
                Section::make('User Information')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->label('User')
                            ->disabledOn('edit'),
                        TextInput::make('email')->disabledOn('edit'),
                        TextInput::make('phone')->disabledOn('edit'),
                        DateTimePicker::make('created_at')
                            ->label('Date of Creation')
                            ->disabled(),
                        TextInput::make('role_name')
                            ->label('Applied As')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                return $record?->user?->role?->name ?? 'N/A';
                            }),
                    ])
                    ->columns(2),
                Section::make('Flat & Building Details')
                    ->schema([
                        Select::make('flat_id')->label('Flat')
                            ->relationship('flat', 'property_number')
                            ->disabled()
                            ->live(),
                        TextInput::make('building')
                            ->formatStateUsing(function ($record) {
                                return Flat::where('id', $record->flat_id)->first()?->building->name;
                            })
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Documents')
                    ->schema([
                        FileUpload::make('passport')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/JPG', 'application/doc', 'application/docx'])
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        FileUpload::make('emirates_document')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/JPG', 'application/doc', 'application/docx'])
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        FileUpload::make('trade_license')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/JPG', 'application/doc', 'application/docx'])
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        FileUpload::make('document')
                            ->label(function (Get $get) {
                                if ($get('document_type') == 'Ejari') {
                                    return 'Tenancy Contract / Ejari';
                                }
                                return $get('document_type');
                            })
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/JPG', 'application/doc', 'application/docx'])
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        DatePicker::make('passport_expiry_date')
                            ->label('Passport Expiry')
                            ->minDate(Carbon::today())
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        DatePicker::make('emirates_document_expiry_date')
                            ->label('Emirates ID Expiry')
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        DatePicker::make('trade_license_expiry_date')
                            ->label('Trade License Expiry')
                            ->minDate(Carbon::today())
                            ->disabled(!(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval'))),
                        DatePicker::make('document_expiry_date')
                            ->label(function (Get $get) {
                                if ($get('document_type') == 'Ejari') {
                                    return 'Tenancy Contract / Ejari';
                                }
                                return $get('document_type');
                            })
                            ->disabled(function (Get $get) use ($user) {
                                return !(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin' || Role::where('id', auth()->user()->role_id)->first()->name == 'OA' || $user->can('update_user::approval')) || $get('document_type') != 'Ejari';
                            })
                            ->hidden(function (Get $get) {
                                return $get('document_type') != 'Ejari';
                            }),
                    ])
                    ->columns(4),
                Section::make('Owners Information')
                    ->schema([
                        Tabs::make('Owners')
                            ->tabs(function ($record) {
                                // Early return if no record
                                if (!$record || !$record->flat_id) {
                                    return [
                                        Tabs\Tab::make('no_data')
                                            ->label('No Data')
                                            ->schema([
                                                Placeholder::make('no_data')
                                                    ->content('No owner data available.')
                                            ])
                                    ];
                                }

                                // Get all flat owners associated with this flat
                                $flatOwners = FlatOwners::where('flat_id', $record->flat_id)->get();

                                if ($flatOwners->isEmpty()) {
                                    return [
                                        Tabs\Tab::make('no_owners')
                                            ->label('No Owners')
                                            ->schema([
                                                Placeholder::make('')
                                                    ->content('No owners found for this flat.')
                                            ])
                                    ];
                                }

                                $tabs = [];

                                // Create a tab for each owner
                                foreach ($flatOwners as $index => $flatOwner) {
                                    $ownerDetail = ApartmentOwner::where('id', $flatOwner->owner_id)->first();

                                    if ($ownerDetail) {
                                        $tabs[] = Tabs\Tab::make("owner_{$index}")
                                            ->label($ownerDetail->name ?? "Owner " . ($index + 1))
                                            ->schema([
                                                Placeholder::make("owner_{$index}_name")
                                                    ->label('Name')
                                                    ->content($ownerDetail->name ?? 'N/A'),
                                                Placeholder::make("owner_{$index}_email")
                                                    ->label('Email')
                                                    ->content($ownerDetail->email ?? 'N/A'),
                                                Placeholder::make("owner_{$index}_phone")
                                                    ->label('Phone')
                                                    ->content($ownerDetail->mobile ?? 'N/A'),
                                                Placeholder::make("owner_{$index}_passport")
                                                    ->label('Passport')
                                                    ->content($ownerDetail->passport ?? 'N/A'),
                                                Placeholder::make("owner_{$index}_emirates_id")
                                                    ->label('Emirates ID')
                                                    ->content($ownerDetail->emirates_id ?? 'N/A'),
                                                Placeholder::make("owner_{$index}_trade_license")
                                                    ->label('Trade License')
                                                    ->content($ownerDetail->trade_license ?? 'N/A'),
                                            ])
                                            ->columns(2);
                                    }
                                }

                                return $tabs;
                            })
                    ]),
                Section::make('Approval Details')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('start_date')
                                ->label('Contract Start Date')
                                ->disabledOn('edit')
                                ->visible(function ($record) {
                                    $role = DB::table('flat_tenants')
                                        ->where('tenant_id', $record->user_id)
                                        ->where('flat_id', $record->flat_id)
                                        ->value('role');
                                    return $role == 'Tenant';
                                })
                                ->hidden(is_numeric(Filament::getTenant()?->id))
                                ->afterStateHydrated(function ($state, $set, $record) {
                                    if ($record) {
                                        $startDate = DB::table('flat_tenants')
                                            ->where('tenant_id', $record->user_id)
                                            ->where('flat_id', $record->flat_id)
                                            ->value('start_date');
                                        if ($startDate) {
                                            $startDate = Carbon::parse($startDate)->format('Y-m-d');
                                            $set('start_date', $startDate);
                                        }
                                    }
                                }),
                            DatePicker::make('end_date')
                                ->label('Contract End Date')
                                ->disabledOn('edit')
                                ->visible(function ($record) {
                                    $role = DB::table('flat_tenants')
                                        ->where('tenant_id', $record->user_id)
                                        ->where('flat_id', $record->flat_id)
                                        ->value('role');
                                    return $role == 'Tenant';
                                })
                                ->hidden(is_numeric(Filament::getTenant()?->id))
                                ->afterStateHydrated(function ($state, $set, $record) {
                                    if ($record) {
                                        $endDate = DB::table('flat_tenants')
                                            ->where('tenant_id', $record->user_id)
                                            ->where('flat_id', $record->flat_id)
                                            ->value('end_date');
                                        if ($endDate) {
                                            $endDate = Carbon::parse($endDate)->format('Y-m-d');
                                            $set('end_date', $endDate);
                                        }
                                    }
                                }),
                            Select::make('status')
                                ->options([
                                    'approved' => 'Approve',
                                    'rejected' => 'Reject',
                                ])
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, $record, Set $set) {
                                    if (
                                        $state === 'approved' &&
                                        DB::table('flat_tenants')
                                            ->where('tenant_id', $record->user_id)
                                            ->where('role', 'Tenant')
                                            ->exists()
                                    ) {
                                        $set('validation_errors', null);
                                    }
                                }),

                            // Add rental details section that shows when status is approved
                            Section::make('Rental Details')
                                ->hidden(function () {
                                    $pm = auth()->user()->role->name === 'Property Manager';
                                    if ($pm) {
                                        return false;
                                    }return true;
                                })
                                ->schema([
                                    TextInput::make('admin_fee')
                                        ->required()
                                        ->label('Contract amount')
                                        ->numeric()
                                        ->lazy()
                                        ->suffix('AED')
                                        ->maxValue(99999999.99) // Adjusted max value to fit within the allowed range
                                        ->validationMessages([
                                            'required' => 'Contract amount is required',
                                            'numeric' => 'Contract amount must be a number',
                                            'max_value' => 'Contract amount cannot exceed 99,999,999.99 AED', // Updated error message
                                        ])
                                        ->afterStateUpdated(function ($get, $set, $state) {
                                            // Get the number of cheques
                                            $numberOfCheques = $get('number_of_cheques');

                                            if ($numberOfCheques && $state) {
                                                // Calculate the default amount per cheque
                                                $defaultAmount = round($state / $numberOfCheques, 2);

                                                // Update cheque amounts
                                                $cheques = $get('cheques');
                                                if (is_array($cheques)) {
                                                    foreach ($cheques as $index => $cheque) {
                                                        // Only update if the amount hasn't been manually modified
                                                        if (!isset($cheque['amount_manually_edited'])) {
                                                            $set("cheques.{$index}.amount", $defaultAmount);
                                                        }
                                                    }
                                                }
                                            }
                                        })
                                        ->disabled(function ($record) {
                                            if (!$record) {
                                                return false;
                                            }

                                            $flatTenant = DB::table('flat_tenants')
                                                ->where('tenant_id', $record->user_id)
                                                ->where('flat_id', $record->flat_id)
                                                ->where('role', 'Tenant')
                                                ->latest()
                                                ->first();
                                            return $flatTenant && RentalDetail::where('flat_tenant_id', $flatTenant->id)
                                                ->exists();
                                        }),

                                    Select::make('number_of_cheques')
                                        ->required()
                                        ->disabled(function ($record) {
                                            if (!$record) {
                                                return false;
                                            }

                                            $flatTenant = DB::table('flat_tenants')
                                                ->where('tenant_id', $record->user_id)
                                                ->where('flat_id', $record->flat_id)
                                                ->where('role', 'Tenant')
                                                ->latest()
                                                ->first();
                                            return $flatTenant && RentalDetail::where('flat_tenant_id', $flatTenant->id)
                                                ->exists();
                                        })
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '6' => '6',
                                            '12' => '12',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(function ($get, $set, $state) {
                                            // Get the admin fee for even distribution
                                            $adminFee = $get('admin_fee');

                                            // Create an array of empty cheque entries
                                            $cheques = array_fill(0, (int) $state, [
                                                'cheque_number' => '',
                                                'amount' => $adminFee ? round($adminFee / $state, 2) : '',
                                                'due_date' => '',
                                                'status' => 'Upcoming',
                                                'mode_payment' => 'Cheque',
                                            ]);

                                            $set('cheques', $cheques);
                                        }),
                                    DatePicker::make('contract_start_date')
                                        ->required()
                                        ->afterStateHydrated(function ($state, $set, $record) {
                                            if ($record) {
                                                $startDate = DB::table('flat_tenants')
                                                    ->where('tenant_id', $record->user_id)
                                                    ->where('flat_id', $record->flat_id)
                                                    ->value('start_date');
                                                if ($startDate) {
                                                    $startDate = Carbon::parse($startDate)->format('Y-m-d');
                                                    $set('contract_start_date', $startDate);
                                                }
                                            }
                                        }),
                                    DatePicker::make('contract_end_date')
                                        ->required()
                                        ->after('contract_start_date')
                                        ->afterStateHydrated(function ($state, $set, $record) {
                                            if ($record) {
                                                $endDate = DB::table('flat_tenants')
                                                    ->where('tenant_id', $record->user_id)
                                                    ->where('flat_id', $record->flat_id)
                                                    ->value('end_date');
                                                if ($endDate) {
                                                    $endDate = Carbon::parse($endDate)->format('Y-m-d');
                                                    $set('contract_end_date', $endDate);
                                                }
                                            }
                                        }),
                                    TextInput::make('advance_amount')
                                        ->required()
                                        ->numeric()
                                        ->live()
                                        ->disabled(fn($record, $state) => static::shouldDisableField($record))
                                        // ->disabled(function (callable $get) {
                                        //     if ($get('contract_status' == 'Contract ended')) {
                                        //         return true;
                                        //     }
                                        // })
                                        ->label('Security Deposit')
                                        ->suffix('AED')
                                        ->maxValue(999999999.99)
                                        ->validationMessages([
                                            'required' => 'Security deposit amount is required',
                                            'numeric' => 'Security deposit must be a number',
                                            'max_value' => 'Security deposit cannot exceed 999,999,999.99 AED',
                                        ]),
                                    Select::make('advance_amount_payment_mode')
                                        // ->disabled(function (callable $get) {
                                        //     if ($get('contract_status' == 'Contract ended')) {
                                        //         return true;
                                        //     }
                                        // })
                                        ->disabled(fn($record, $state) => static::shouldDisableField($record))
                                        ->live()
                                        ->required()
                                        ->label('Security Deposit Payment Mode')
                                        ->options([
                                            'Online' => 'Online',
                                            'Cheque' => 'Cheque',
                                            'Cash' => 'Cash',
                                        ]),
                                    TextInput::make('admin_charges')
                                        ->nullable()
                                        ->suffix('AED')
                                        ->minValue(0)
                                        ->numeric()
                                        ->maxLength(10)
                                        ->maxValue(999999999.99)
                                        ->placeholder('Enter the Admin charges')
                                        ->disabled(fn($record, $state) => static::shouldDisableField($record)),

                                    TextInput::make('brokerage')
                                        ->nullable()
                                        ->suffix('AED')
                                        ->minValue(0)
                                        ->numeric()
                                        ->maxLength(10)
                                        ->maxValue(999999999.99)
                                        ->placeholder('Enter the Brokerage amount')
                                        ->disabled(fn($record, $state) => static::shouldDisableField($record)),

                                    TextInput::make('other_charges')
                                        ->nullable()
                                        ->suffix('AED')
                                        ->minValue(0)
                                        ->numeric()
                                        ->maxLength(10)
                                        ->maxValue(999999999.99)
                                        ->placeholder('Enter Other Charges')
                                        ->disabled(fn($record, $state) => static::shouldDisableField($record)),

                                    Select::make('contract_status') // Changed from 'status' to 'contract_status'
                                        ->default('Active')
                                        ->required()
                                        ->label('Contract Status')
                                        ->options([
                                            'Active' => 'Active',
                                            'Contract ended' => 'Ended',
                                            'Contract extended' => 'Extended',
                                        ])
                                        ->live()
                                        ->afterStateUpdated(function ($state, $record) {
                                            if ($record) {
                                                $flatTenant = DB::table('flat_tenants')
                                                    ->where('tenant_id', $record->user_id)
                                                    ->where('flat_id', $record->flat_id)
                                                    ->where('role', 'Tenant')
                                                    ->latest()
                                                    ->first();

                                                if ($flatTenant) {
                                                    RentalDetail::where('flat_tenant_id', $flatTenant->id)
                                                        ->update(['status' => $state]);
                                                }
                                            }
                                        }),
                                ])
                                ->columns(2)
                                ->visible(function (Get $get, $record) {
                                    return $get('status') === 'approved' &&
                                        DB::table('flat_tenants')
                                            ->where('tenant_id', $record->user_id)
                                            ->where('flat_id', $record->flat_id)
                                            ->where('role', 'Tenant')
                                            ->exists();
                                }),

                            Section::make('Cheque Details')
                                ->hidden(function () {
                                    $pm = auth()->user()->role->name === 'Property Manager';
                                    if ($pm) {
                                        return false;
                                    }return true;
                                })
                                ->schema([
                                    Repeater::make('cheques')
                                        ->schema([
                                            TextInput::make('cheque_number')
                                                ->required()
                                                ->numeric()
                                                ->minLength(6)
                                                ->maxLength(12)
                                                ->validationMessages([
                                                    'required' => 'Cheque number is required',
                                                    'numeric' => 'Cheque number must contain only numbers',
                                                    'min_length' => 'Cheque number must be at least 6 digits',
                                                    'max_length' => 'Cheque number cannot exceed 12 digits',
                                                ])
                                                ->placeholder('Enter cheque number'),

                                            TextInput::make('amount')
                                                ->required()
                                                ->numeric()
                                                ->maxValue(999999999.99)
                                                ->validationMessages([
                                                    'required' => 'Cheque amount is required',
                                                    'numeric' => 'Cheque amount must be a number',
                                                    'max_value' => 'Cheque amount cannot exceed 999,999,999.99 AED',
                                                ])
                                                ->placeholder('Enter amount')
                                                ->afterStateUpdated(function ($set) {
                                                    // Mark that the amount has been manually edited
                                                    $set('amount_manually_edited', true);
                                                }),

                                            DatePicker::make('due_date')
                                                ->required()
                                                ->placeholder('Select due date'),

                                            Select::make('mode_payment')
                                                ->required()
                                                ->default('Cheque')
                                                ->options([
                                                    'Online' => 'Online',
                                                    'Cheque' => 'Cheque',
                                                    'Cash' => 'Cash',
                                                ]),
                                        ])
                                        ->columns(2)
                                        ->addable(false)
                                        ->deletable(false)
                                        ->defaultItems(0)
                                        ->disabled(function ($record) {
                                            if (!$record) {
                                                return false;
                                            }

                                            $flatTenant = DB::table('flat_tenants')
                                                ->where('tenant_id', $record->user_id)
                                                ->where('flat_id', $record->flat_id)
                                                ->where('role', 'Tenant')
                                                ->latest()
                                                ->first();
                                            return $flatTenant && RentalDetail::where('flat_tenant_id', $flatTenant->id)->exists();
                                        }),
                                ])
                                ->visible(function (Get $get, $record) {
                                    return $get('status') === 'approved' &&
                                        DB::table('flat_tenants')
                                            ->where('tenant_id', $record->user_id)
                                            ->where('role', 'Tenant')
                                            ->exists();
                                }),
                        ]),
                        Textarea::make('remarks')
                            ->maxLength(250)
                            ->rows(5)
                            ->required()
                            ->visible(function (Get $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            })->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn($record) => $record->user->full_name ?? 'No Name')
                    ->default('--')
                    ->limit(20),
                Tables\Columns\TextColumn::make('flat.property_number')->label('Flat Number')
                    ->default('--')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flat.building.name')->label('Building')
                    ->default('--')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date of creation')
                    ->default('--')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.role.name')->label('Role')
                    ->default('--')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'warning' => fn($state) => $state === null || $state === 'NA',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-clock' => fn($state) => $state === null || $state === 'NA',
                        'heroicon-o-check-circle' => 'approved',
                    ])
                    ->formatStateUsing(fn($state) => $state === null || $state === 'NA' ? 'Pending' : ucfirst($state))
                    ->default('--'),
            ])
            ->filters([
                // SelectFilter::make('status')
                //     ->options([
                //         'approved'    => 'Approved',
                //         'rejected'    => 'Rejected',
                //         'pending'     => 'Pending',
                //         'NA'          => 'Not Applicable',
                //     ])
                SelectFilter::make('building_id')
                    ->relationship('flat.building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending', // Label for NULL
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value']) || empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'pending') {
                            return $query->whereNull('status');
                        }

                        return $query->where('status', $data['value']);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserApprovals::route('/'),
            'create' => Pages\CreateUserApproval::route('/create'),
            'view' => Pages\ViewUserApproval::route('/{record}'),
            'edit' => Pages\EditUserApproval::route('/{record}/edit'),
        ];
    }

    // Add this method to handle form submission
    protected function handleFormSubmission($data)
    {
        try {
            if ($data['status'] === 'approved') {
                // Validate cheque amounts
                if (isset($data['cheques']) && isset($data['admin_fee'])) {
                    $chequesSum = collect($data['cheques'])->sum('amount');
                    if ($chequesSum != $data['admin_fee']) {
                        Notification::make()
                            ->title('Incorrect Cheque Amounts')
                            ->body('The sum of cheque amounts must equal the contract amount')
                            ->danger()
                            ->send();
                        return false;
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to create rental details: ' . $e->getMessage())
                ->danger()
                ->send();
            return false;
        }
    }

    // Add this helper method to the class
    private static function shouldDisableField($record): bool
    {
        if (!auth()->user()?->role->name === 'Property Manager') {
            return true;
        }

        if (!$record) {
            return false;
        }

        $flatTenant = DB::table('flat_tenants')
            ->where('tenant_id', $record->user_id)
            ->where('flat_id', $record->flat_id)
            ->where('role', 'Tenant')
            ->latest()
            ->first();
        return $flatTenant && RentalDetail::where('flat_tenant_id', $flatTenant->id)->exists();
    }
}
