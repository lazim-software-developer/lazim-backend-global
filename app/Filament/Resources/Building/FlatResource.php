<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FlatResource\Pages;
use App\Filament\Resources\Building\FlatResource\RelationManagers;
use App\Models\Building\Flat;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlatResource extends Resource
{
    protected static ?string $model = Flat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Flats';
    protected static ?string $navigationGroup = 'Flat Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([
                    TextInput::make('property_number')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Number'),
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Flat Number'),
                Tables\Columns\TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->label('Building')
                    ->preload()
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
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FlatResource\RelationManagers\FlatDomesticHelpRelationManager::class,
            FlatResource\RelationManagers\FlatTenantRelationManager::class,
            FlatResource\RelationManagers\FlatVisitorRelationManager::class,
            FlatResource\RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlats::route('/'),
            //'create' => Pages\CreateFlat::route('/create'),
            'edit' => Pages\EditFlat::route('/{record}/edit'),
        ];
    }
}
