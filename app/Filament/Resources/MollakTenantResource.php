<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MollakTenantResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use Filament\Forms\Components\Section;
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

class MollakTenantResource extends Resource
{
    protected static ?string $model = MollakTenant::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $modelLabel      = 'Tenant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->disabled(),
                        TextInput::make('emirates_id')
                            ->label('Emirates ID')
                            ->formatStateUsing(function ($state) {
                                return $state ?: 'NA';
                            })
                            ->disabled(),
                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                    ])
                    ->columns(2), // Two columns in this section

                Section::make('Contract Details')
                    ->schema([
                        TextInput::make('contract_number')
                            ->label('Contract number')
                            ->disabled(),
                        TextInput::make('license_number')
                            ->label('License number')
                            ->formatStateUsing(function ($state) {
                                return $state ?: 'NA';
                            })
                            ->default('NA')
                            ->disabled(),
                        TextInput::make('start_date')
                            ->label('Start date')
                            ->disabled(),
                        TextInput::make('end_date')
                            ->label('End date')
                            ->disabled(),
                        TextInput::make('contract_status')
                            ->label('Contract status')
                            ->disabled(),
                    ])
                    ->columns(2), // Two columns in this section

                Section::make('Property Information')
                    ->schema([
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->label('Building')
                            ->disabled(),
                        Select::make('flat_id')
                            ->relationship('flat', 'property_number')
                            ->label('Flat')
                            ->disabled(),
                    ])
                    ->columns(2), // Two columns in this section
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->limit(25)
                    ->default('NA'),
                TextColumn::make('contract_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('emirates_id')
                    ->label('Emirates ID')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('license_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('mobile')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('email')
                    ->searchable()
                    ->default('NA'),
                // TextColumn::make('start_date')
                //     ->searchable()
                //     ->default('NA'),
                // TextColumn::make('end_date')
                //     ->searchable()
                //     ->default('NA'),
                // TextColumn::make('contract_status')
                //     ->searchable()
                //     ->default('NA'),
                // TextColumn::make('building.name')
                //     ->searchable()
                //     ->default('NA'),
                // TextColumn::make('flat.property_number')
                //     ->searchable()
                //     ->default('NA')
                //     ->label('Unit Number'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
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
                        Select::make('property_number')
                            ->placeholder('Search Unit Number')->label('Unit')
                            ->options(function () {
                                if (auth()->user()->role->first()->name == 'Admin') {
                                    return Flat::pluck('property_number', 'property_number');
                                } else {
                                    return Flat::where('owner_association_id', auth()->user()->owner_association_id)->pluck('property_number', 'property_number');

                                }
                            })
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['property_number'])) {
                            return $query->whereHas('flat', function ($query) use ($data) {
                                $query->where('property_number', $data['property_number']);
                            });
                        }
                        return $query;
                    }),
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
            'index' => Pages\ListMollakTenants::route('/'),
            'view'  => Pages\ViewMollakTenant::route('/{record}'),
        ];
    }
}
