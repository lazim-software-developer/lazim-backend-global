<?php

namespace App\Filament\Resources\User;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use App\Filament\Resources\User\OwnerResource\Pages;

class OwnerResource extends Resource
{
    protected static ?string $model           = ApartmentOwner::class;
    protected static ?string $modelLabel      = 'Owners';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $tenantRelationshipName = 'owners';
    

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])
                ->schema([

                    TextInput::make('owner_number')
                        ->numeric()
                        ->required()
                        ->placeholder('Owner Number'),
                    TextInput::make('name')
                        ->rules(['max:100', 'string'])
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('primary_owner_mobile')
                        ->rules([
                            'regex:/^\+?[1-9]\d{1,14}$/',
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null; // Retrieve the selected building ID from the record
                                    
                                    $query = ApartmentOwner::where('primary_owner_mobile', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This primary owner mobile number is already registered with this owner association.");
                                    }
                                };
                            }
                        ])
                        ->required()
                        ->numeric()
                        ->placeholder('Primary Owner Mobile'),
                    TextInput::make('primary_owner_email')
                    ->rules([
                        'min:6',
                        'max:30', 
                        'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        function ($record) {
                            return function (string $attribute, $value, Closure $fail) use ($record) {
                                if (empty($value)) {
                                    return;
                                }
                        
                                $ownerAssociationId = auth()->user()->owner_association_id;
                                $buildingId = $record['building_id'] ?? null;
                                $query = ApartmentOwner::where('primary_owner_email', $value)
                                    ->where('owner_association_id', $ownerAssociationId);

                                // Add a condition to check the building ID
                                if (!empty($buildingId)) {
                                    $query->where('building_id', $buildingId);
                                }
                                
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                $query->whereNull('deleted_at');
                                if ($query->exists()) {
                                    $fail("This primary owner email is already registered with this owner association.");
                                }
                            };
                        }
                    ])
                    ->required()
                    ->placeholder('Primary Owner Email'),
                    TextInput::make('mobile')
                        ->rules([
                            'regex:/^\+?[1-9]\d{1,14}$/',
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null; // Retrieve the selected building ID from the record
                                    
                                    $query = ApartmentOwner::where('mobile', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This owner mobile number is already registered with this owner association.");
                                    }
                                };
                            }
                        ])
                        ->required()
                        ->numeric()
                        ->placeholder('Mobile'),
                    TextInput::make('email')
                    ->rules([
                        'min:6',
                        'max:30', 
                        'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        function ($record) {
                            return function (string $attribute, $value, Closure $fail) use ($record) {
                                if (empty($value)) {
                                    return;
                                }
                        
                                $ownerAssociationId = auth()->user()->owner_association_id;
                                $buildingId = $record['building_id'] ?? null;
                                $query = ApartmentOwner::where('email', $value)
                                    ->where('owner_association_id', $ownerAssociationId);

                                // Add a condition to check the building ID
                                if (!empty($buildingId)) {
                                    $query->where('building_id', $buildingId);
                                }
                                
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                $query->whereNull('deleted_at');
                                if ($query->exists()) {
                                    $fail("This owner email is already registered with this owner association.");
                                }
                            };
                        }
                    ])
                    ->required()
                    ->placeholder('Email'),
                    TextInput::make('passport')
                        ->rules([
                            'string',
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $query = ApartmentOwner::where('passport', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This passport number is already registered with this owner association.");
                                    }
                                };
                            }
                        ])
                        ->required()
                        ->placeholder('Passport'),
                    TextInput::make('emirates_id')
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $query = ApartmentOwner::where('emirates_id', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This emirates id is already registered with this owner association.");
                                    }
                                };
                            }
                        ])
                        ->numeric()
                        ->required()
                        ->label('Emirates ID'),
                    TextInput::make('trade_license')
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (empty($value)) {
                                        return;
                                    }
                            
                                    $ownerAssociationId = auth()->user()->owner_association_id;
                                    $buildingId = $record['building_id'] ?? null;
                                    $query = ApartmentOwner::where('trade_license', $value)
                                        ->where('owner_association_id', $ownerAssociationId);
                                    
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    // Add a condition to check the building ID
                                    if (!empty($buildingId)) {
                                        $query->where('building_id', $buildingId);
                                    }
                                    $query->whereNull('deleted_at');
                                    if ($query->exists()) {
                                        $fail("This trade license id is already registered with this owner association.");
                                    }
                                };
                            }
                        ])
                        ->numeric()
                        ->required()
                        ->label('Trade License Number'),
                    Hidden::make('owner_association_id')
                        ->default(auth()->user()?->owner_association_id),
                    Hidden::make('resource')
                    ->default('Lazim'),
                    Select::make('status')
                        ->options([
                            '1' => 'Approve',
                            '0' => 'Reject',
                            '2' => 'Pending'
                        ])
                        ->default(null)
                        ->required()
                        ->label('Status'),
                    Select::make('owner_status')
                    ->options([
                        'VIP' => 'VIP',
                        'normal' => 'Normal'
                    ])
                    ->default(null)
                    ->required()
                    ->label('Owner Status'),
                    Select::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        }else{
                        return Building::where('owner_association_id', auth()->user()->owner_association_id)
                            // ->where('resource', 'Default')
                            ->pluck('name', 'id');
                        }
                    })
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->required(function (?int $state): bool {
                        return $state !== null && $state === 1;
                    })
                    ->label('Select Building')
                    ->placeholder('Select a Building') // Change the placeholder text
                    ->afterStateUpdated(function ($state, $set) {
                        if (is_array($state) && isset($state['building_id']) && is_array($state['_previous']) && isset($state['_previous']['building_id'])) {
                            if ($state['building_id'] !== $state['_previous']['building_id']) {
                                $set('flatOwners', []);
                            }
                        }
                    }),
                    
                    Repeater::make('flatOwners')
                    ->relationship()
                    ->schema([
                        Select::make('flat_id')
                            ->label('Unit Number')
                            ->required()
                            ->options(function ($get) {
                                $buildingId = $get('../../building_id');
                                if (!$buildingId) return [];

                                // Get all selected flat IDs except the current one
                                $currentFlatId = $get('flat_id');
                                $selectedFlats = collect($get('../../flatOwners'))
                                    ->pluck('flat_id')
                                    ->filter()
                                    ->reject(function ($id) use ($currentFlatId) {
                                        return $id === $currentFlatId;
                                    })
                                    ->toArray();

                                return Flat::where('building_id', $buildingId)
                                    // ->where('status', 1)
                                    ->whereNotIn('id', $selectedFlats)
                                    ->get()
                                    ->mapWithKeys(function ($flat) {
                                        return [$flat->id => $flat->property_number];
                                    });
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === null) {
                                    $set('flat_id', null);
                                }
                            })
                            ->preload()
                            ->searchable()
                    ])
                        // ->columnSpan([
                        //     'sm' => 1,
                        //     'md' => 1,
                        //     'lg' => 2,
                        // ]),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->label('Name')
                    ->limit(50)
                    ->formatStateUsing(function ($state, $record): string {
                        // Get owner_status directly from the record
                        $ownerStatus = $record->owner_status ?? '';
                        
                        // Check if we have both state and status
                        if ($state && $ownerStatus === 'VIP') {
                            return $state . " (VIP)";
                        }
                        
                        // Fallback to just state if status is not VIP
                        return $state ?? '';
                    })
                    ->html(),
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
                Tables\Columns\TextColumn::make('resource')
                    ->searchable()
                    ->default('NA')
                    ->label('Resource')
                    ->limit(50),
                ViewColumn::make('Property Number')->view('tables.columns.apartment-ownerflat')->alignCenter(),
                ViewColumn::make('Building')->view('tables.columns.apartment-ownerbuilding')->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('delete')
                ->button()
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;
    
                    if ($role === 'Admin' || $role === 'Property Manager') {
                        return true;
                    }
                })
                ->action(function ($record) {
                    $deletedRecords = FlatOwners::where('owner_id', $record->id)->get();
                    $record->deleted_at = now();
                    $record->save();
                    $connection = DB::connection(env('SECOND_DB_CONNECTION'));
                    if($deletedRecords){
                        foreach($deletedRecords as $deletedRecord){
                            $deletedRecord->deleted_at = now();
                            $deletedRecord->save();
                            $connection->table('customers')->where('email', $record->email)->where('flat_id', $deletedRecord->flat_id)->update(['deleted_at' => now()]);
                        }
                    }
                    Notification::make()
                        ->title('Owner Deleted Successfully')
                        ->success()
                        ->send()
                        ->duration('4000');
                })
                ->requiresConfirmation()
                ->modalHeading('Are you sure you want to delete this ?')
                ->modalButton('Delete'),
                // Action::make('Notify Owner')
                // ->button()
                // ->action(function (array $data,$record){
                //     $flatID = FlatOwners::where('owner_id',$record->id)->value('flat_id');
                //     $buildingname = Flat::where('id',$flatID)->first()->building->name;
                //     $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                //     $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                //     $OaName = Filament::getTenant()->name;

                //     if($record->email==null){
                //         Notification::make()
                //         ->title('Email not found')
                //         ->success()
                //         ->send();
                //     }else{
                //         WelcomeNotificationJob::dispatch($record->email, $record->name,$buildingname,$emailCredentials,$OaName);
                //         Notification::make()
                //         ->title("Successfully Sent Mail")
                //         ->success()
                //         ->body("Sent mail to owner asking him to download the app.")
                //         ->send();
                //     }
                // })
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->searchable()
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->pluck('name', 'id');
                                }
                            })
                            ->placeholder('Select Building'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['Building']),
                            function ($query) use ($data) {
                                $query->whereHas('flatOwners.flat', function ($query) use ($data) {
                                    $query->where('building_id', $data['Building']);
                                });
                            }
                        );
                    }),
                Filter::make('Property Number')
                    ->form([
                        TextInput::make('property_number')
                            ->placeholder('Search Unit Number')->label('Unit'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['property_number'])) {
                            $query->whereHas('flatOwners.flat', function ($query) use ($data) {
                                $query->where('property_number', 'like', '%' . $data['property_number'] . '%');
                            });
                        }
                        return $query;
                    }),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //UserDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            'create' => Pages\CreateOwner::route('/create'),
            'view'  => Pages\ViewOwner::route('/{record}'),
            'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }
}