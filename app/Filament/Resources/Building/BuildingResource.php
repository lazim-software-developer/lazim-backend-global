<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use App\Models\Building\Building;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Property Management';
    protected static bool $shouldRegisterNavigation = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                ])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),

                    TextInput::make('property_group_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Property Group Id')
                        ->unique(
                            'buildings',
                            'property_group_id',
                            fn (?Model $record) => $record
                        ),

                    TextInput::make('address_line1')
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->placeholder('Address Line1'),

                    TextInput::make('address_line2')
                        ->rules(['max:255', 'string'])
                        ->nullable()
                        ->placeholder('Address Line2'),
                    Hidden::make('owner_association_id')
                        ->default(auth()->user()->owner_association_id),

                    TextInput::make('area')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Area'),

                    Select::make('city_id')
                        ->rules(['exists:cities,id'])
                        ->required()
                        ->preload()
                        ->relationship('cities', 'name')
                        ->searchable()
                        ->placeholder('City'),

                    TextInput::make('lat')
                        ->rules(['numeric'])
                        ->placeholder('Lat'),

                    TextInput::make('lng')
                        ->rules(['numeric'])
                        ->placeholder('Lng'),

                    TextInput::make('description')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Description'),

                    TextInput::make('floors')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Floors')

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('property_group_id')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('address_line1')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('address_line2')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('area')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('cities.name')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('lat')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('lng')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('floors')
                    ->toggleable()
                    ->default('NA')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            BuildingResource\RelationManagers\FacilityBookingsRelationManager::class,
            BuildingResource\RelationManagers\ServiceBookingsRelationManager::class,
            BuildingResource\RelationManagers\BudgetRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            BuildingResource\RelationManagers\ComplaintsRelationManager::class,
            BuildingResource\RelationManagers\ServicesRelationManager::class,
            // BuildingResource\RelationManagers\DocumentsRelationManager::class,
            BuildingResource\RelationManagers\FacilitiesRelationManager::class,
            BuildingResource\RelationManagers\FlatsRelationManager::class,
            BuildingResource\RelationManagers\VendorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
