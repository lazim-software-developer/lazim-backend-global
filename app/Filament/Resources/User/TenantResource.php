<?php

namespace App\Filament\Resources\User;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\Building\Flat;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\User\TenantResource\Pages;

class TenantResource extends Resource
{
    protected static ?string $model           = MollakTenant::class;
    protected static ?string $modelLabel      = 'Tenants';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $tenantRelationshipName = 'tenants';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])
                ->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('contract_number')
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $flatId = $record['flat_id'] ?? null;
                                    $query = MollakTenant::where('contract_number', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    // Add a condition to check the Flat ID
                                    if (!empty($flatId)) {
                                        $query->where('flat_id', $flatId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This Tenant contract number is already registered with this building, Unit & owner association.");
                                    }
                                };
                            }
                        ])
                        ->numeric()
                        ->required()
                        ->placeholder('Contract Number'),
                    TextInput::make('emirates_id')
                        ->numeric()
                        ->required()
                        ->rules(['regex:/^\d{15}$/'])
                        ->validationMessages([
                            'regex' => 'The Emirates ID must be exactly 15 numeric digits.'
                        ])
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $flatId = $record['flat_id'] ?? null;
                                    $query = MollakTenant::where('emirates_id', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    // Add a condition to check the Flat ID
                                    if (!empty($flatId)) {
                                        $query->where('flat_id', $flatId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This Tenant emirates id is already registered with this building, Unit & owner association.");
                                    }
                                };
                            }
                        ])
                        ->placeholder('Emirates Id'),
                    TextInput::make('passport')
                    ->required()
                    ->rules('alpha_num')
                    ->placeholder('Passport Number')
                    ->rules([
                        function ($record) {
                            return function (string $attribute, $value, Closure $fail) use ($record) {
                                if (empty($value)) {
                                    return;
                                }
                        
                                $ownerAssociationId = auth()->user()->owner_association_id;
                                $buildingId = $record['building_id'] ?? null;
                                $flatId = $record['flat_id'] ?? null;
                                $query = MollakTenant::where('passport', $value)
                                    ->where('owner_association_id', $ownerAssociationId);
                                
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                // Add a condition to check the building ID
                                if (!empty($buildingId)) {
                                    $query->where('building_id', $buildingId);
                                }
                                // Add a condition to check the Flat ID
                                if (!empty($flatId)) {
                                    $query->where('flat_id', $flatId);
                                }
                                $query->whereNull('deleted_at');
                                if ($query->exists()) {
                                    $fail("This Tenant passport number is already registered with this building, Unit & owner association.");
                                }
                            };
                        }
                    ]),
                    TextInput::make('license_number')
                    ->rules('alpha_num')
                    ->placeholder('License Number'),
                    TextInput::make('mobile')
                        ->rules(['regex:/^\+?[1-9]\d{1,14}$/'])
                        ->required()
                        ->placeholder('Mobile')
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $flatId = $record['flat_id'] ?? null;
                                    $query = MollakTenant::where('mobile', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    // Add a condition to check the Flat ID
                                    if (!empty($flatId)) {
                                        $query->where('flat_id', $flatId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This Tenant mobile number is already registered with this building, Unit & owner association.");
                                    }
                                };
                            }
                        ]),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                        ->required()
                        ->label('Email')
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $flatId = $record['flat_id'] ?? null;
                                    $query = MollakTenant::where('email', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    // Add a condition to check the Flat ID
                                    if (!empty($flatId)) {
                                        $query->where('flat_id', $flatId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This Tenant email is already registered with this building, Unit & owner association.");
                                    }
                                };
                            }
                        ]),
                    Select::make('building_id')
                    ->rules(['exists:buildings,id'])
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::where('status', 1)
                            ->pluck('name', 'id');
                        }else{
                        return Building::where('owner_association_id', auth()->user()->owner_association_id)
                            ->where('status', 1)
                            ->where('resource', 'Default')
                            ->pluck('name', 'id');
                        }
                    })
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->placeholder('Select a Building'),
                    Select::make('flat_id')
                    ->rules(['exists:flats,id'])
                    ->required()
                    ->options(function ($get) {
                        $buildingId = $get('building_id');
                        if (!$buildingId) return [];
                        
                        return Flat::where('building_id', $buildingId)
                            ->where('status', 1)
                            ->get()
                            ->mapWithKeys(function ($flat) {
                                return [$flat->id => $flat->property_number];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->label('Unit Number')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state === null) {
                            $set('flat_id', null);
                        }
                    }),
                    DatePicker::make('start_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Start Date')
                        ->minDate(now()->startOfDay()),
                    DatePicker::make('end_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('End Date')
                        ->minDate(now()->startOfDay()),
                    Hidden::make('owner_association_id')
                    ->default(auth()->user()?->owner_association_id),
                    Select::make('contract_status')
                        ->options([
                            'pass auditing'  => 'Pass Auditing',
                            'active'         => 'Active',
                            'under auditing' => 'Under Auditing',
                        ])
                        ->searchable()
                        ->live(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->defaultGroup('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->label('Name')
                    ->limit(50),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->default('NA')
                    ->label('Mobile')
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->default('NA')
                    ->label('Email')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Buildings'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Unit Number'),
                Tables\Columns\TextColumn::make('contract_status')
                    ->searchable()
                    ->default('NA')
                    ->label('Contract status')
                    ->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('delete')
                    ->button()
                    ->action(function ($record,) {
                        $record->delete();

                        Notification::make()
                            ->title('Tenants Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
                // Action::make('Notify Tenant')
                // ->button()
                // ->action(function ($record){
                //     $buildingname = $record->building->name;
                //     $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                //     $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                //     $OaName = Filament::getTenant()->name;

                //     if($record->email==null){
                //         Notification::make()
                //         ->title('Email not found')
                //         ->success()
                //         ->send();
                //     }else{
                //        WelcomeNotificationJob::dispatch($record->email, $record->name,$buildingname,$emailCredentials,$OaName);
                //         Notification::make()
                //         ->title("Successfully Sent Mail")
                //         ->success()
                //         ->body("Sent mail to tenant asking him to download the app.")
                //         ->send();
                //     }
                // })
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                Filter::make('Property Number')
                    ->form([
                        TextInput::make('property_number')
                            ->placeholder('Search Unit Number')->label('Unit'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['property_number'])) {
                            return $query->whereHas('flat', function ($query) use ($data) {
                                $query->where('property_number', $data['property_number']);
                            });
                        }
                        return $query;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // UserDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
            'view'  => Pages\ViewTenant::route('/{record}'),
        ];
    }
}
