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
use Filament\Forms\Components\Toggle;
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
                    'lg' => 1,])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),
                       
                    TextInput::make('property_group_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Property Group Id'),

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
                        ->relationship('cities', 'name')
                        ->searchable()
                        ->placeholder('City'),

                    TextInput::make('lat')
                        ->rules(['numeric'])
                        ->placeholder('Lat'),

                    TextInput::make('lng')
                        ->rules(['numeric'])
                        ->placeholder('Lng'),

                    RichEditor::make('description')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Description'),

                    TextInput::make('floors')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Floors'),

                    Toggle::make('allow_postupload')
                        ->rules(['boolean'])
                        ->default(0)
                        ->label('Allow Post Upload')
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
                ->limit(50),
            Tables\Columns\TextColumn::make('property_group_id')
                ->toggleable()
                ->searchable()
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
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('cities.name')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('lat')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('lng')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('description')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('floors')
                ->toggleable()
                ->searchable(),
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
