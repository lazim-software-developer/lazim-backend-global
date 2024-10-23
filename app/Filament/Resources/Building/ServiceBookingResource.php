<?php

namespace App\Filament\Resources\Building;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\Building\ServiceBooking;
use Filament\Tables\Actions\EditAction;
use App\Models\Building\FacilityBooking;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Building\ServiceBookingResource\Pages;
use App\Filament\Resources\Building\ServiceBookingResource\RelationManagers;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Personal Service Booking';
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
                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return Building::all()->pluck('name', 'id');
                                }
                                else{
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->pluck('name', 'id');
                                }    
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->disabledOn('edit')
                            ->searchable()
                            ->placeholder('Building'),

                        TextInput::make('flat_id')
                            ->formatStateUsing(function($state){
                                return Flat::where('id',$state)->value('property_number');
                            })
                            ->label('Flat')
                            ->disabledOn('edit'),

                        Select::make('bookable_id')
                            ->options(
                                DB::table('services')
                                    ->where('type', 'inhouse')
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
                            ->options(function () {
                                $roleId = Role::whereIn('name',['tenant','owner'])->pluck('id')->toArray();

                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return User::whereIn('role_id', $roleId)->pluck('first_name', 'id'); 
                                }
                                else{
                                    return User::whereIn('role_id', $roleId)->where('owner_association_id',auth()->user()?->owner_association_id)->pluck('first_name', 'id');
                                }
                                })
                            ->searchable()
                            ->disabledOn('edit')
                            ->preload()
                            ->placeholder('User'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Date'),
                        TimePicker::make('start_time')
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Start Time'),
                        TimePicker::make('end_time')
                            ->default('NA')
                            ->disabledOn('edit')
                            ->placeholder('End Time'),
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
                Tables\Columns\TextColumn::make('flat_id')
                    ->formatStateUsing(function($state){
                        return Flat::where('id',$state)->value('property_number');
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50)
                    ->label('Flat'),
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
                Filter::make('filter')
                    ->form([
                        Select::make('building_id')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                                    return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->label('Building')
                            ->reactive(),
                        Select::make('flat_id')
                            ->label('Flat')
                            ->options(function (callable $get) {
                                if (empty($get('building_id'))) {
                                    return [];
                                } else {
                                    return Flat::where('building_id', $get('building_id'))
                                        ->pluck('property_number', 'id');
                                }
                            })
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['building_id']) && $data['building_id']) {
                            $query->where('building_id', $data['building_id']);
                        }
            
                        if (isset($data['flat_id']) && $data['flat_id']) {
                            $query->where('flat_id', $data['flat_id']);
                        }
                    }),
            ])
            ->filtersFormColumns(3) 
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
            'index' => Pages\ListServiceBookings::route('/'),
            'create' => Pages\CreateServiceBooking::route('/create'),
            // 'view' => Pages\ViewServiceBooking::route('/{record}'),
            'edit' => Pages\EditServiceBooking::route('/{record}/edit'),
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
