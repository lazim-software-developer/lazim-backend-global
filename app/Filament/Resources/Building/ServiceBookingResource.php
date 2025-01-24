<?php
namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\ServiceBookingResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel      = 'Personal Service Booking';
    protected static ?string $navigationGroup = 'Property Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([

                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else{
                                    $buildings = DB::table('building_owner_association')
                                        ->where('owner_association_id', auth()->user()->owner_association_id)
                                        ->where('active', true)
                                        ->pluck('building_id');
                                    return Building::whereIn('id', $buildings)->pluck('name', 'id');
                                }
                            })
                            ->afterStateUpdated(fn(callable $set) => $set('flat_id', null))
                            ->reactive()
                            ->required()
                            ->preload()
                            ->disabledOn('edit')
                            ->searchable()
                            ->placeholder('Building'),

                        Select::make('flat_id')
                            ->native(false)
                            ->required()
                            ->helperText(function (callable $get) {
                                if ($get('building_id') === null) {
                                    return 'Please select a building first';
                                }
                            })
                            ->disabledOn('edit')
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('user_id', null))
                            ->placeholder('Select Flat')
                            ->relationship('building.flats', 'property_number')
                            ->label('Flat')
                            ->options(function (callable $get) {
                                $pmFlats = DB::table('property_manager_flats')
                                    ->where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->where('active', true)
                                    ->pluck('flat_id')
                                    ->toArray();

                                if (auth()->user()->role->name == 'Property Manager') {
                                    return DB::table('flats')
                                        ->whereIn('id', $pmFlats)
                                        ->where('building_id', $get('building_id'))
                                        ->pluck('property_number', 'id');
                                }

                                return DB::table('flats')
                                    ->where('building_id', $get('building_id'))
                                    ->pluck('property_number', 'id');
                            })
                            ->preload(),

                        Select::make('bookable_id')
                            ->options(
                                DB::table('services')
                                    ->where('type', 'inhouse')
                                    ->where('active', true)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->label('Service')
                            ->disabledOn('edit')
                            ->required()
                            ->label('Service'),

                        Hidden::make('bookable_type')
                            ->default('App\Models\Master\Service'),

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('user', 'first_name')
                            ->options(function (callable $get) {
                                $roleId = Role::whereIn('name', ['tenant', 'owner'])->pluck('id')->toArray();

                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return User::whereIn('role_id', $roleId)->pluck('first_name', 'id');
                                } else {
                                    $flatId    = $get('flat_id');
                                    $residents = FlatTenant::where('flat_id', $flatId)
                                        ->where('active', true)
                                        ->get()
                                        ->map(function ($tenant) {
                                            $role            = $tenant->role;
                                            $roleDescription = $role == 'Owner' ? 'Owner' : 'Tenant';
                                            return [
                                                'id'   => $tenant->tenant_id,
                                                'name' => $tenant->user->first_name . ' (' . $roleDescription . ')',
                                            ];
                                        });
                                    return $residents->pluck('name', 'id')->toArray();
                                }
                            })
                            ->searchable()
                            ->disabledOn('edit')
                            ->preload()
                            ->placeholder('User'),

                        Grid::make([
                            'sm' => 2,
                            'md' => 3,
                            'lg' => 3,
                        ])
                            ->schema([
                                DatePicker::make('date')
                                    ->rules(['date'])
                                    ->required()
                                    ->disabledOn('edit')
                                    ->placeholder('Date'),
                                TimePicker::make('start_time')
                                    ->required()
                                    ->disabledOn('edit')
                                    ->placeholder('Start Time'),
                                // TimePicker::make('end_time')
                                //     ->default('NA')
                                //     ->disabledOn('edit')
                                //     ->placeholder('End Time'),
                            ]),
                        // TextInput::make('remarks')
                        //     ->default('NA')
                        //     ->disabledOn('edit')
                        //     ->required(),
                        // TextInput::make('reference_number')
                        //     ->rules(['numeric'])
                        //     ->default('0')
                        //     ->disabledOn('edit')
                        //     ->required()
                        //     ->numeric()
                        //     ->placeholder('References Number'),
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('bookable.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50)
                    ->label('Service'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->searchable()
                    ->default('NA')
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->searchable()
                    ->default('NA')
                    ->time(),
                // Tables\Columns\TextColumn::make('reference_number')
                //     ->default('NA')
                //     ->searchable(),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else{
                            $buildings = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');
                            return Building::whereIn('id', $buildings)->pluck('name', 'id');
                        }

                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServiceBookings::route('/'),
            'create' => Pages\CreateServiceBooking::route('/create'),
            // 'view' => Pages\ViewServiceBooking::route('/{record}'),
            'edit'   => Pages\EditServiceBooking::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_building::service::booking');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_building::service::booking');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_building::service::booking');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_building::service::booking');
    }
}
