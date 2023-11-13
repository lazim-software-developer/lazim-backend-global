<?php

namespace App\Filament\Resources\Master\CityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
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

    public function table(Table $table): Table
    {
        return $table
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
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
