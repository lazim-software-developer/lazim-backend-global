<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\BuildingResource\Pages;
use App\Filament\Resources\Building\BuildingResource\RelationManagers;
use App\Models\Building\Building;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    'lg' => 2,])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name')
                       ,
                    TextInput::make('unit_number')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->unique(
                            'buildings',
                            'unit_number',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Unit Number'),

                    RichEditor::make('address_line1')
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->placeholder('Address Line1'),

                    RichEditor::make('address_line2')
                        ->rules(['max:255', 'string'])
                        ->nullable()
                        ->placeholder('Address Line2'),

                    TextInput::make('area')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Area'),

                    Select::make('city_id')
                        ->rules(['exists:cities,id'])
                        ->required()
                        ->relationship('city', 'id')
                        ->searchable()
                        ->placeholder('City'),

                    TextInput::make('lat')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Lat'),

                    TextInput::make('lng')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Lng'),

                    RichEditor::make('description')
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
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('unit_number')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('address_line1')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('address_line2')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('area')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('city.id')
                ->toggleable()
                ->limit(50),
            Tables\Columns\TextColumn::make('lat')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('lng')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('description')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('floors')
                ->toggleable()
                ->searchable(true, null, true),
        ])
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
            BuildingResource\RelationManagers\AttendanceRelationManager::class,
            BuildingResource\RelationManagers\BuildingPocsRelationManager::class,
            BuildingResource\RelationManagers\ComplaintsRelationManager::class,
            BuildingResource\RelationManagers\DocumentsRelationManager::class,
            BuildingResource\RelationManagers\FacilitiesRelationManager::class,
            BuildingResource\RelationManagers\FlatsRelationManager::class,
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
