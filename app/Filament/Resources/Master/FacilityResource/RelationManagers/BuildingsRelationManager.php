<?php

namespace App\Filament\Resources\Master\FacilityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('unit_number')
                        ->rules(['max:50', 'string'])
                        ->unique(
                            'buildings',
                            'unit_number',
                            fn (?Model $record) => $record
                        )
                        ->placeholder('Unit Number')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    RichEditor::make('address_line1')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Address Line1')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    RichEditor::make('address_line2')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Address Line2')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('area')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Area')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('city_id')
                        ->rules(['exists:cities,id'])
                        ->relationship('cities', 'id')
                        ->searchable()
                        ->placeholder('City')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('lat')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Lat')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('lng')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Lng')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    RichEditor::make('description')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Description')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('floors')
                        ->rules(['numeric'])
                        ->numeric()
                        ->placeholder('Floors')
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
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\TextColumn::make('unit_number')->limit(50),
                Tables\Columns\TextColumn::make('address_line1')->limit(50),
                Tables\Columns\TextColumn::make('address_line2')->limit(50),
                Tables\Columns\TextColumn::make('area')->limit(50),
                Tables\Columns\TextColumn::make('city.id')->limit(50),
                Tables\Columns\TextColumn::make('lat')->limit(50),
                Tables\Columns\TextColumn::make('lng')->limit(50),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('floors'),
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
