<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Vehicle;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Building\FlatTenant;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VehicleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\VehicleResource\RelationManagers;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Vehicles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('building')
                    ->required()
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('name', 'id');
                        }
                    })
                    ->native(false)
                    ->visibleOn('create')
                    ->live(),

                TextInput::make('building_name')
                    ->label('Building')
                    ->formatStateUsing(function ($record) {
                        if ($record && $record->flat_id) {
                            $buildingId = Flat::where('id', $record->flat_id)->value('building_id');
                            return Building::where('id', $buildingId)->value('name');
                        }
                        return null;
                    })
                    ->visibleOn('edit')
                    ->disabled(),

                Select::make('flat_id')
                    ->searchable()
                    ->required()
                    ->label('Flat')
                    ->options(function (callable $get) {
                        return Flat::where('building_id', $get('building'))->pluck('property_number', 'id');
                    })
                    ->getSearchResultsUsing(fn(string $search, callable $get): array => Flat::where('building_id', $get('building'))->where('property_number', 'like', "%{$search}%")->pluck('property_number', 'id')->toArray())
                    ->formatStateUsing(function ($state) {
                        return Flat::where('id', $state)->value('property_number');
                    })
                    ->disabledOn('edit')
                    ->native(false)
                    ->live(),
                TextInput::make('vehicle_number')->alphaNum()->unique(
                    'vehicles',
                    'vehicle_number',
                    fn(?Model $record) => $record
                )->required()->minLength(2)->maxLength(10),
                TextInput::make('parking_number')
                    // ->prefix(function (Get $get) {                
                    //         return Flat::find($get('flat_id'))?->property_number . ' -';
                    // })
                    ->helperText('Unit no. would be added as prefix')
                    ->alphaNum()->minLength(2)->maxLength(10)->disabledOn('edit')
                    ->unique(
                        'vehicles',
                        'parking_number',
                        fn(?Model $record) => $record
                    )
                    ->required(),
                Select::make('user_id')->label('Resident')->searchable()->preload()->required()->disabledOn('edit')
                    ->relationship('user', 'first_name')
                    ->options(function (Get $get) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return User::all()->whereIn('role_id', Role::whereIn('name', ['Tenant', 'Owner'])->pluck('id'))->pluck('first_name', 'id');
                        } else {
                            // $user_id =  User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['Tenant', 'Owner'])->pluck('id'))->pluck('id');
                            $flatResidentsId = FlatTenant::where('flat_id', $get('flat_id'))->pluck('tenant_id');

                            return User::whereIn('id', $flatResidentsId)->pluck('first_name', 'id');
                        }
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn(Builder $query) => $query->orderBy('created_at', 'desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('vehicle_number')->searchable(),
                TextColumn::make('parking_number')->searchable(),
                TextColumn::make('user.first_name')->label('Resident')->searchable(),
                TextColumn::make('Building')
                    ->getStateUsing(function ($record) {
                        $building = Flat::where('id', $record->flat_id)->value('building_id');
                        return Building::where('id', $building)->value('name');
                    }),
                TextColumn::make('flat_id')->label('Flat')
                    ->formatStateUsing(function ($record) {
                        return Flat::where('id', $record->flat_id)->value('Property_number');
                    }),
            ])
            ->filters([
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->native(false)
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->pluck('name', 'id');
                                }
                            })
                            ->searchable()
                            ->reactive()  // Reactivity added here
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('flat', null); // Reset flat when building changes
                            }),
                        Select::make('flat')
                            ->native(false)
                            ->options(function (callable $get) {
                                $buildingId = $get('Building'); // Get selected building ID
                                if (!$buildingId) {
                                    return [];
                                }
                                return Flat::where('building_id', $buildingId)->pluck('property_number', 'id');
                            })
                            ->searchable()
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['Building'])) {
                            $flatIds = Flat::where('building_id', $data['Building'])->pluck('id');
                            $query->whereIn('flat_id', $flatIds);
                        }
                        if (!empty($data['flat'])) {
                            $query->where('flat_id', $data['flat']);
                        }

                        return $query;
                    })
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->searchPlaceholder('Search Vehicle/Resident');
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
