<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Vehicles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('vehicle_number')->alphaNum()->unique(
                    'vehicles',
                    'vehicle_number',
                    fn(?Model $record) => $record
                )->required(),
                TextInput::make('makani_number')->alphaNum()->unique(
                    'vehicles',
                    'makani_number',
                    fn(?Model $record) => $record
                )->required(),

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
                    ->getSearchResultsUsing(fn (string $search,callable $get): array => Flat::where('building_id', $get('building'))->where('property_number', 'like', "%{$search}%")->pluck('property_number', 'id')->toArray())
                    ->formatStateUsing(function ($state) {
                        return Flat::where('id', $state)->value('property_number');
                    })
                    ->disabledOn('edit')
                    ->native(false),

                Select::make('user_id')->label('Resident')->searchable()->preload()->required()->disabledOn('edit')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return User::all()->whereIn('role_id', Role::whereIn('name', ['Tenant', 'Owner'])->pluck('id'))->pluck('first_name', 'id');
                        } else {
                            return User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['Tenant', 'Owner'])->pluck('id'))->pluck('first_name', 'id');
                        }
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn(Builder $query) => $query->orderBy('created_at', 'desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('vehicle_number')->searchable(),
                TextColumn::make('makani_number'),
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
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['Building']) {
                            $flatIds = Flat::where('building_id', $data['Building'])->pluck('id');
                            return $query->whereIn('flat_id', $flatIds);
                        }

                        return $query;
                    }),
            ])
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
