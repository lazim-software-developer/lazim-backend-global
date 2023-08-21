<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\BuildingPocResource\Pages;
use App\Filament\Resources\Building\BuildingPocResource\RelationManagers;
use App\Models\Building\BuildingPoc;
use Filament\Forms;
use Filament\Forms\Components\Grid;
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
    protected static ?string $navigationLabel = 'Pocs';
    protected static ?string $navigationGroup = 'Building Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->required()
                        ->relationship('building', 'name')
                        ->searchable()
                        ->placeholder('Building')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('role_name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Role Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('escalation_level')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Escalation Level')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('emergency_contact')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
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
                ->limit(50),
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
