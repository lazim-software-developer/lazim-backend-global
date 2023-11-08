<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\BuildingPocResource\Pages;
use App\Filament\Resources\Building\BuildingPocResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\User\User;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingPocResource extends Resource
{
    protected static ?string $model = BuildingPoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Security';
    protected static ?string $navigationGroup = 'Property Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->options(function () {
                            return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                ->select('id', 'name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->reactive()
                        ->options(function () {
                            return User::where('role_id', 12)
                                ->select('id','first_name')
                                ->pluck('first_name','id')
                                ->toArray();
                        })
                        ->required()
                        ->preload()
                        ->searchable()
                        ->placeholder('User'),
                    Hidden::make('role_name')
                        ->default('security'),
                    Hidden::make('escalation_level')
                        ->default('1'),
                    Toggle::make('emergency_contact')
                        ->rules(['boolean'])
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->toggleable()
                    ->limit(50)
                    ->label('Owner Association'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('role_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('escalation_level')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\IconColumn::make('emergency_contact')
                    ->toggleable()
                    ->boolean(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildingPocs::route('/'),
            'create' => Pages\CreateBuildingPoc::route('/create'),
            'edit' => Pages\EditBuildingPoc::route('/{record}/edit'),
        ];
    }
}
