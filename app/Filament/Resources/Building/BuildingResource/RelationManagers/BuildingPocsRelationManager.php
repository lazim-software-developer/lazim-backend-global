<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BuildingPocsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingPocs';
    protected static ?string $modelLabel = 'Security';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Security';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                ])->schema([

                            Select::make('user_id')
                                ->rules(['exists:users,id'])
                                ->relationship('user', 'first_name')
                                ->reactive()
                                ->unique(
                                    'building_pocs',
                                    'user_id',
                                )
                                ->options(function () {
                                    return User::where('role_id', 12)
                                        ->select('id', 'first_name')
                                        ->pluck('first_name', 'id')
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
                            Hidden::make('active')
                                ->default(true),
                            Hidden::make('building_id')
                                ->default(function (RelationManager $livewire) {
                                    return $livewire->ownerRecord->id;
                                }),
                            Toggle::make('emergency_contact')
                                ->rules(['boolean'])
                        ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->limit(50)
                    ->label('Owner Association'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->limit(50),
                Tables\Columns\TextColumn::make('role_name')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('escalation_level')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('emergency_contact')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
}
