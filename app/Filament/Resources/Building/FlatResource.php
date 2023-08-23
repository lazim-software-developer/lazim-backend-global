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
                    TextInput::make('number')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Number'),


                    TextInput::make('floor')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Floor'),

                    // Select::make('building_id')
                    //     ->rules(['exists:buildings,id'])
                    //     ->required()
                    //     ->relationship('building', 'name')
                    //     ->searchable()
                    //     ->placeholder('Building'),


                    TextInput::make('description')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Description'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->toggleable()
                    ->searchable(true, null, true),
                Tables\Columns\TextColumn::make('floor')
                    ->toggleable()
                    ->searchable(true, null, true),
                Tables\Columns\TextColumn::make('building.name')->label('Building Name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
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
            'create' => Pages\CreateFlat::route('/create'),
            'edit' => Pages\EditFlat::route('/{record}/edit'),
        ];
    }
}
