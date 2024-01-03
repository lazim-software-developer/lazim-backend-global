<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingPocsRelationManager extends RelationManager
{
    protected static string $relationship = 'pocs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->searchable()
                        ->placeholder('Building')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('role_name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Role Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('escalation_level')
                        ->rules(['max:50', 'string'])
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')->limit(50),
                Tables\Columns\TextColumn::make('role_name')->limit(50),
                Tables\Columns\TextColumn::make('escalation_level')->limit(50),
                Tables\Columns\IconColumn::make('active'),
                Tables\Columns\IconColumn::make('emergency_contact'),
            ])
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
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
